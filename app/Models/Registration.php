<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Registration extends Model
{
    protected $fillable = ['event_id','code','status','checked_in_at','approved_by'];
    protected $casts   = ['checked_in_at' => 'datetime'];

    public const ST_PENDING  = 'pending';
    public const ST_APPROVED = 'approved';
    public const ST_REJECTED = 'rejected';

    public function event(){ return $this->belongsTo(Event::class); }
    public function values(){ return $this->hasMany(FormFieldValue::class); }

    public function value(string $fieldName, $default=null)
    {
        return optional(
            $this->values->firstWhere('field.name', $fieldName)
        )->value ?? $default;
    }

    public function fieldValues()
    {
        return $this->hasMany(FormFieldValue::class, 'registration_id');
    }

    public function seatAssignment() {
    return $this->hasOne(\App\Models\SeatAssignment::class);
}

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    protected static function booted(): void
    {
        static::deleting(function (Registration $registration) {
            if ($registration->code) {
                $path = "qrcodes/{$registration->code}.png";
                Storage::disk('public')->delete($path);
            }
        });
    }
}
