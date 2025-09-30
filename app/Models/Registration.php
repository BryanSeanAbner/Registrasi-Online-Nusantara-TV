<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Registration extends Model
{
    use HasFactory; 
    protected $fillable=[
        'event_id',
        'name',
        'email',
        'phone',
        'company',
        'qr_code',
        'checked_in_at'
    ];  
    protected $casts=[
        'checked_in_at'=>'datetime'
    ];
    public function event() { return $this->belongsTo(Event::class); }
    public function scopeChecked($q) { return $q->whereNotNull('checked_in_at'); }
}
