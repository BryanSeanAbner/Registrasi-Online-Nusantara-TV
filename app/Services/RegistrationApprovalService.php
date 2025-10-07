<?php

namespace App\Services;

use App\Models\Registration;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Jobs\SendWaMessageJob;

class RegistrationApprovalService
{
    public function approve(Registration $registration, ?string $code = null): Registration
    {
        if ($registration->status === Registration::ST_APPROVED && $registration->code) {
            $this->notifyInfo('Sudah Disetujui', 'Pendaftaran ini sebelumnya sudah disetujui.');
            return $registration;
        }

        $code = $code ?: 'REG-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));

        $registration->update([
            'status' => Registration::ST_APPROVED,
            'code'   => $code,
        ]);

        $qrPayload = $code;
        $png = QrCode::format('png')->size(512)->margin(1)->generate($qrPayload);

        $path = "qrcodes/{$code}.png";
        Storage::disk('public')->put($path, $png);

        try {
            dispatch(new SendWaMessageJob($registration));
            $this->notifySuccess('Disetujui ✅', 'Kode & pesan WhatsApp berhasil dikirim ke peserta.');
        } catch (\Throwable $e) {
            $this->notifyError(
                'WA Gagal Dikirim',
                'Approval berhasil, namun pengiriman WhatsApp gagal: ' . $e->getMessage()
            );
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
            $this->notifyInfo('Terlalu Sering', 'Tunggu sebentar (≤60 detik) sebelum kirim ulang lagi.');
            return;
        }

        try {
            dispatch(new SendWaMessageJob($registration));
            Cache::put($cacheKey, true, now()->addSeconds(60));
            $this->notifySuccess('WA Dikirim Ulang', 'Pesan WhatsApp berhasil dikirim ulang ke peserta.');
        } catch (\Throwable $e) {
            $this->notifyError('WA Gagal Dikirim', 'Kirim ulang gagal: '.$e->getMessage());
        }
    }

    public function sendWaMessage(Registration $registration): void
    {
        if (!filter_var(env('WA_ENABLED', false), FILTER_VALIDATE_BOOL)) {
            $this->notifyInfo('WA Dimatikan', 'Pengiriman WA di-skip karena WA_ENABLED=false.');
            return;
        }

        $url       = env('WA_API_URL');
        $apiKey    = env('WA_API_KEY');
        $numberKey = env('WA_NUMBER_KEY');

        if (!$url || !$apiKey || !$numberKey) {
            throw new \RuntimeException('WA config incomplete: please set WA_API_URL, WA_API_KEY, WA_NUMBER_KEY.');
        }

        $rawPhone = optional(
            $registration->fieldValues
                ->first(fn($fv) => str_contains(strtolower($fv->field->name ?? ''), 'no_wa'))
        )->value;

        if (blank($rawPhone)) {
            throw new \RuntimeException('Tidak ditemukan field yang mengandung kata "no_wa".');
        }

        $phone      = $this->normalizeIndoMsisdn($rawPhone);
        $eventTitle = optional($registration->event)->title ?? '-';
        $name = optional(
            $registration->fieldValues
                ->first(fn($fv) => str_contains(strtolower($fv->field->name ?? ''), 'name_user'))
        )->value;

        $msg = <<<MSG
        🎉 *Selamat, {$name}!* 

        Pendaftaran kamu telah *DISETUJUI* ✅

        📍 *Acara:* {$eventTitle}
        🎟️ *Kode Tiket:* {$registration->code}

        Silakan simpan kode ini dan tunjukkan *QR Code* saat check-in di lokasi.
        Kami tunggu kehadiranmu di acara nanti! ✨
        MSG;

        $payload = [
            "api_key"          => $apiKey,
            "number_key"       => $numberKey,
            "phone_no"         => $phone,
            "message"          => $msg,
            "url"              => "https://c04291120374.ngrok-free.app/t/{$registration->code}/qrcode/preview",
            "wait_until_send"  => "1",
        ];

        $response = Http::asJson()
            ->acceptJson()
            ->timeout(20)
            ->connectTimeout(5)
            ->withoutRedirecting()
            ->withOptions([
                'force_ip_resolve' => 'v4',
                'headers'          => ['Connection' => 'close'],
                'verify' => false, // hanya aktifkan di DEV bila cert bermasalah
            ])
            ->post($url, $payload);
        $responseBody = $response->body();
        
        if ($response->failed()) {
            throw new \RuntimeException('WA API error (' . $response->status() . '): ' . $response->body());
        }

        $data = json_decode($responseBody, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $statusCode = $data['status'] ?? null;
            $statusText = strtolower((string)($data['message'] ?? ''));

            if ($statusCode != 200 && $statusText !== 'success') {
                throw new \RuntimeException("WA API logical error in {$phone} :  {$responseBody}");
            }
        } else {
            if (!empty($responseBody) && $responseBody !== 'OK') {
                throw new \RuntimeException("WA API unexpected response in {$phone} :  {$responseBody}");
            }
        }
    }

    private function normalizeIndoMsisdn(string $input): string
    {
        $digits = preg_replace('/\D+/', '', $input ?? '');

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }
        if (str_starts_with($digits, '62')) {
            return $digits;
        }
        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }
        return $digits;
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
            if ($body) $n->body($body);
            if (method_exists($n, $type)) $n->{$type}();
            $n->send();
        } catch (\Throwable) {
            // Aman di luar konteks Filament (CLI/job)
        }
    }
}