<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scan extends Model
{
    protected $fillable = [
        'registration_id',
        'code',
        'result',
        'location',
        'scanned_by',
    ];

    // relasi
    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function scannedBy()
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }
}
