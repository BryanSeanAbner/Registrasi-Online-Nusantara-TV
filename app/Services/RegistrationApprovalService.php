<?php

namespace App\Services;

use App\Models\Registration;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RegistrationApprovalService
{
    public function approve(Registration $registration, ?string $code = null): Registration
    {
        if ($registration->status === Registration::ST_APPROVED && $registration->code) {
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

        return $registration->refresh();
    }

    public function reject(Registration $registration, ?string $reason = null): Registration
    {
        $registration->update(['status' => Registration::ST_REJECTED]);
        return $registration;
    }
}