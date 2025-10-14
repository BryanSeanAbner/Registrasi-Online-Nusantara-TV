<?php

namespace App\Services;

use App\Jobs\SendWaMessageJob;
use App\Models\Registration;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RegistrationApprovalService
{
    private ?string $apiSendMsg;
    private ?string $apiSendMsgImg;
    private ?string $apiKey;
    private ?string $numberKey;

    public function __construct()
    {
        $this->apiSendMsg    = config('wa.api_send_msg');
        $this->apiSendMsgImg = config('wa.api_send_msg_img');
        $this->apiKey     = config('wa.api_key');
        $this->numberKey  = config('wa.number_key');
    }

    public function getParticipantName(Registration $registration): ?string
    {
        return $this->getFieldValueByEventRole($registration, 'full_name_field_id')
            ?? $this->getFieldValueByRole($registration, 'full_name')
            ?? $this->guessNameField($registration);
    }

    public function getParticipantPhone(Registration $registration, bool $normalize = true): ?string
    {
        $rawPhone = $this->getFieldValueByEventRole($registration, 'wa_phone_field_id')
            ?? $this->getFieldValueByRole($registration, 'wa_phone')
            ?? $this->guessPhoneField($registration);

        if (blank($rawPhone)) {
            return null;
        }

        return $normalize ? $this->normalizeIndoMsisdn((string) $rawPhone) : (string) $rawPhone;
    }

    public function approve(Registration $registration, ?string $code = null): Registration
    {
        if ($registration->status === Registration::ST_APPROVED && $registration->code) {
            $this->notifyInfo('Sudah Disetujui', 'Pendaftaran ini sebelumnya sudah disetujui.');
            return $registration;
        }

        $code = $code ?: 'REG-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8));

        $registration->update([
            'status'      => Registration::ST_APPROVED,
            'code'        => $code,
            'approved_by' => Auth::id(),
        ]);

        $png = QrCode::format('png')->size(512)->margin(1)->generate($code);
        Storage::disk('public')->put("qrcodes/{$code}.png", $png);

        $phone = $this->getParticipantPhone($registration);

        try {
            $msgId = null;
            try {
                $msgRow = \App\Models\WaMessage::create([
                    'blast_id'        => null,
                    'event_id'        => (int) $registration->event_id,
                    'registration_id' => (int) $registration->id,
                    'phone'           => $this->getParticipantPhone($registration) ?? null,
                    'code'            => $code,
                    'status'          => 'queued',
                ]);
                $msgId = $msgRow->id;
            } catch (\Throwable) {
                // error
            }

            dispatch(new SendWaMessageJob($registration, $msgId, Auth::id(), $phone));
            $this->notifySuccess('Disetujui', 'Kode dan pesan WhatsApp berhasil dikirim ke peserta.');
        } catch (\Throwable $e) {
            $this->notifyError('WA Gagal Dikirim', 'Approval berhasil, namun pengiriman WhatsApp gagal: ' . $e->getMessage());
        }

        return $registration->refresh();
    }

    public function reject(Registration $registration, ?string $reason = null): Registration
    {
        $registration->update(['status' => Registration::ST_REJECTED]);
        return $registration;
    }

    public function resendWa(Registration $registration): void
    {
        if ($registration->status !== Registration::ST_APPROVED || blank($registration->code)) {
            $this->notifyError('Gagal Kirim Ulang', 'Hanya pendaftaran yang sudah disetujui yang bisa dikirimi ulang.');
            return;
        }

        $cacheKey = "wa:resend:{$registration->id}";
        if (Cache::has($cacheKey)) {
            $this->notifyInfo('Terlalu Sering', 'Tunggu sebentar (60 detik) sebelum kirim ulang lagi.');
            return;
        }

        $phone = $this->getParticipantPhone($registration);

        try {
            $msgId = null;
            try {
                $msgRow = \App\Models\WaMessage::create([
                    'blast_id'        => null,
                    'event_id'        => (int) $registration->event_id,
                    'registration_id' => (int) $registration->id,
                    'phone'           => $this->getParticipantPhone($registration) ?? null,
                    'code'            => (string) $registration->code,
                    'status'          => 'queued',
                ]);
                $msgId = $msgRow->id;
            } catch (\Throwable) {}

            dispatch(new SendWaMessageJob($registration, $msgId, Auth::id(), $phone));
            Cache::put($cacheKey, true, now()->addSeconds(60));
            $this->notifySuccess('WA Dikirim Ulang', 'Pesan WhatsApp berhasil dikirim ulang ke peserta.');
        } catch (\Throwable $e) {
            $this->notifyError('WA Gagal Dikirim', 'Kirim ulang gagal: ' . $e->getMessage());
        }
    }

    public function sendCustomWaMessage(Registration $registration, string $message, ?string $url = null): void
    {
        if (! filter_var((string) config('wa.enabled', false), FILTER_VALIDATE_BOOL)) {
            $this->notifyInfo('WA Dimatikan', 'Pengiriman WA di-skip karena WA_ENABLED=false.');
            return;
        }

        $endpoint  = $this->apiSendMsg;
        $apiKey    = $this->apiKey;
        $numberKey = $this->numberKey;

        if (! $endpoint || ! $apiKey || ! $numberKey) {
            throw new \RuntimeException('WA config incomplete: set wa.api_send_msg, wa.api_send_msg_img, wa.api_key, wa.number_key.');
        }

        $registration->loadMissing(['event', 'fieldValues.field']);

        $phone = $this->getParticipantPhone($registration);
        if (blank($phone)) {
            throw new \RuntimeException('Tidak ditemukan field nomor WhatsApp.');
        }

        $payload = [
            'api_key'         => $apiKey,
            'number_key'      => $numberKey,
            'phone_no'        => $phone,
            'message'         => $message,
            'wait_until_send' => '1',
        ];

        if ($url) {
            $payload['url'] = $url;
        }

        $response = Http::asJson()
            ->acceptJson()
            ->timeout(60)
            ->connectTimeout(5)
            ->withoutRedirecting()
            ->withOptions([
                'force_ip_resolve' => 'v4',
                'headers'          => ['Connection' => 'close'],
                'verify'           => false,
            ])
            ->post($endpoint, $payload);

        if ($response->failed()) {
            throw new \RuntimeException('WA API error (' . $response->status() . '): ' . $response->body());
        }

        $responseBody = $response->body();
        $data = json_decode($responseBody, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $statusCode = $data['status'] ?? null;
            $statusText = strtolower((string) ($data['message'] ?? ''));

            if ($statusCode != 200 && $statusText !== 'success') {
                $msg = (string) ($data['message'] ?? 'Unknown error');
                throw new \RuntimeException("WA API logical error for {$phone}: {$msg}");
            }
        } else {
            if (! empty($responseBody) && $responseBody !== 'OK') {
                throw new \RuntimeException("WA API unexpected response for {$phone}: {$responseBody}");
            }
        }
    }

    public function sendWaMessage(Registration $registration): void
    {
        if (! filter_var((string) config('wa.enabled', false), FILTER_VALIDATE_BOOL)) {
            $this->notifyInfo('WA Dimatikan', 'Pengiriman WA di-skip karena WA_ENABLED=false.');
            return;
        }

        $apiKey    = $this->apiKey;
        $numberKey = $this->numberKey;
        $endpoint  = $this->apiSendMsgImg;

        if (! $endpoint || ! $apiKey || ! $numberKey) {
            throw new \RuntimeException('WA config incomplete: set wa.api_send_msg or wa.api_send_msg_img, wa.api_key, wa.number_key.');
        }

        $phone = $this->getParticipantPhone($registration);
        if (blank($phone)) {
            throw new \RuntimeException('Tidak ditemukan field nomor WhatsApp. Tandai peran field sebagai "Nomor WhatsApp" pada Form Field, atau pastikan ada field bertipe Phone.');
        }
        $event      = optional($registration->event);
        $eventTitle = $event->title ?? '-';
        $name       = $this->getParticipantName($registration);

        // Build WA message from template (per event) with placeholders.
        $qrUrl = env('WA_LINK_IMG')
            ? env('WA_LINK_IMG') . "/t/{$registration->code}/qrcode/preview"
            : url("/t/{$registration->code}/qrcode/preview");

        $template = (string) data_get($event, 'brand.wa_template');
        if (blank($template)) {
            $template = "Selamat, {name}!\n\nPendaftaran kamu telah DISETUJUI.\n\nAcara: {event}\Lokasi: {location}\nKode Tiket: {code}\n\nSimpan kode ini dan tunjukkan QR Code saat check-in di lokasi.\nSampai jumpa di acara!";
        }
        $msg = strtr($template, [
            '{name}'        => (string) $name,
            '{event}'       => (string) $eventTitle,
            '{code}'        => (string) $registration->code,
            '{location}'    => (string) $event->venue,
            '{qr_url}'      => (string) $qrUrl,
        ]);

        $payload = [
            'api_key'         => $apiKey,
            'number_key'      => $numberKey,
            'phone_no'        => $phone,
            'message'         => $msg,
            'url'             => $qrUrl,
            'wait_until_send' => '1',
        ];

        $response = Http::asJson()
            ->acceptJson()
            ->timeout(60)
            ->connectTimeout(5)
            ->withoutRedirecting()
            ->withOptions([
                'force_ip_resolve' => 'v4',
                'headers'          => ['Connection' => 'close'],
                'verify'           => false, // aktifkan true di production bila cert OK
            ])
            ->post($endpoint, $payload);

        $responseBody = $response->body();

        if ($response->failed()) {
            throw new \RuntimeException('WA API error (' . $response->status() . '): ' . $response->body());
        }

        $data = json_decode($responseBody, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $statusCode = $data['status'] ?? null;
            $statusText = strtolower((string) ($data['message'] ?? ''));

            if ($statusCode != 200 && $statusText !== 'success') {
                $msg = (string) ($data['message'] ?? 'Unknown error');
                throw new \RuntimeException("WA API logical error for {$phone}: {$msg}");
            }
        } else {
            if (! empty($responseBody) && $responseBody !== 'OK') {
                throw new \RuntimeException("WA API unexpected response for {$phone}: {$responseBody}");
            }
        }
    }

    private function normalizeIndoMsisdn(string $input): string
    {
        $digits = preg_replace('/\D+/', '', $input ?? '');
        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }
        if (str_starts_with($digits, '62')) {
            return $digits;
        }
        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }
        return $digits;
    }

    private function getFieldValueByRole(Registration $registration, string $role): mixed
    {
        $fv = $registration->fieldValues->first(function ($fv) use ($role) {
            $meta = (array) ($fv->field->meta ?? []);
            return ($meta['role'] ?? null) === $role;
        });
        return $fv?->value;
    }

    private function guessPhoneField(Registration $registration): ?string
    {
        // Prefer type=phone
        $fv = $registration->fieldValues->first(function ($fv) {
            return ($fv->field->type ?? null) === 'phone' && filled($fv->value);
        });
        if ($fv) return (string) $fv->value;

        // Fallback: name/label contains wa / whatsapp / phone
        $fv = $registration->fieldValues->first(function ($fv) {
            $name  = strtolower((string) ($fv->field->name ?? ''));
            $label = strtolower((string) ($fv->field->label ?? ''));
            return (str_contains($name, 'wa') || str_contains($label, 'wa') ||
                    str_contains($name, 'whatsapp') || str_contains($label, 'whatsapp') ||
                    str_contains($name, 'phone') || str_contains($label, 'phone') ||
                    str_contains($name, 'telepon') || str_contains($label, 'telepon'))
                && filled($fv->value);
        });
        return $fv?->value;
    }

    private function guessNameField(Registration $registration): ?string
    {
        // Prefer obvious name labels
        $fv = $registration->fieldValues->first(function ($fv) {
            $name  = strtolower((string) ($fv->field->name ?? ''));
            $label = strtolower((string) ($fv->field->label ?? ''));
            return (str_contains($name, 'name') || str_contains($label, 'name') ||
                    str_contains($name, 'nama') || str_contains($label, 'nama'))
                && filled($fv->value);
        });
        if ($fv) return (string) $fv->value;

        // Fallback: first non-empty text/email
        $fv = $registration->fieldValues->first(function ($fv) {
            return in_array(($fv->field->type ?? ''), ['text','email']) && filled($fv->value);
        });
        return $fv?->value;
    }

    private function notifySuccess(string $title, ?string $body = null): void
    {
        $this->notify('success', $title, $body);
    }

    private function notifyError(string $title, ?string $body = null): void
    {
        $this->notify('danger', $title, $body);
    }

    private function notifyInfo(string $title, ?string $body = null): void
    {
        $this->notify('info', $title, $body);
    }

    private function notify(string $type, string $title, ?string $body = null): void
    {
        try {
            $n = Notification::make()->title($title);
            if ($body) {
                $n->body($body);
            }
            if (method_exists($n, $type)) {
                $n->{$type}();
            }
            $n->send();
        } catch (\Throwable) {
            // Aman di luar konteks Filament (CLI/job)
        }
    }

    private function getFieldValueByEventRole(Registration $registration, string $brandRoleKey): mixed
    {
        $event = $registration->event;
        if (! $event) return null;
        $fieldId = data_get($event->brand, 'roles.' . $brandRoleKey);
        if (! $fieldId) return null;

        $fv = $registration->fieldValues->first(fn ($v) => (int) $v->field_id === (int) $fieldId);
        return $fv?->value;
    }
}
