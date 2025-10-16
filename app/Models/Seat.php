<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    protected $fillable = [
        'event_id',
        'table_id',
        'section',
        'row',
        'col',
        'label',
        'type',
        'status',
        'price'
    ];
    
    public function event(){ 
        return $this->belongsTo(Event::class); 
    }

    public function assignment(){ 
        return $this->hasOne(SeatAssignment::class); 
    }

    public function table()
    {
        return $this->belongsTo(\App\Models\SeatTable::class, 'table_id');
    }
}
