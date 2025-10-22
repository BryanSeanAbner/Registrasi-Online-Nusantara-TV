<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaBlast extends Model
{
    protected $fillable = [
        'event_id', 'initiated_by', 'template', 'include_qr',
        'status', 'total', 'dispatched', 'sent', 'failed', 'started_at', 'finished_at',
    ];

    protected $casts = [
        'include_qr' => 'boolean',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function messages()
    {
        return $this->hasMany(WaMessage::class, 'blast_id');
    }
}

