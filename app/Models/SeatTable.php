<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeatTable extends Model
{
    protected $fillable = [
        'event_id', 'label', 'capacity', 'meta', 'notes',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
    
    public function seats()
    {
        return $this->hasMany(Seat::class, 'table_id');
    }
}
