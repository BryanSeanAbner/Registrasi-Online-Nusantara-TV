<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeatAssignment extends Model
{
    protected $fillable = [
        'event_id',
        'seat_id',
        'registration_id',
        'assigned_by',
        'assigned_at'
    ];

    public function seat(){ 
        return $this->belongsTo(Seat::class); 
    }

    public function registration(){ 
        return $this->belongsTo(Registration::class); 
    }
    
    public function event(){ 
        return $this->belongsTo(Event::class); 
    }
}
