<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaMessage extends Model
{
    protected $fillable = [
        'blast_id', 'event_id', 'registration_id', 'phone', 'code',
        'status', 'error', 'provider_response', 'dispatched_at', 'sent_at', 'failed_at',
    ];

    protected $casts = [
        'dispatched_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function blast()
    {
        return $this->belongsTo(WaBlast::class, 'blast_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }
}

