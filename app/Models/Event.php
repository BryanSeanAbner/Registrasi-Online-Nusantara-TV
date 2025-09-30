<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'starts_at', 'ends_at', 'venue', 'brand', 'is_published'];

    protected $casts = [
        'brand' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function formFields()
    {
        return $this->hasMany(FormField::class);
    }
}
