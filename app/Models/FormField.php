<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormField extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id','label','name','type',
        'is_required','is_toggleable','is_hidden_by_default',
        'show_in_scan','show_in_form','sort_order',
        'placeholder','help_text','meta',
    ];

    protected $casts = [
        'is_required' => 'bool',
        'is_toggleable' => 'bool',
        'is_hidden_by_default' => 'bool',
        'show_in_scan' => 'bool',
        'show_in_form' => 'bool',
        'meta' => 'array',
    ];

    public function getMetaAttribute($value)
    {
        if (is_array($value)) return $value;
        $arr = json_decode($value ?? '', true);
        return $arr ?: [];
    }

    public function setMetaAttribute($value): void
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $this->attributes['meta'] = json_encode($decoded ?? []);
        } elseif (is_array($value)) {
            $this->attributes['meta'] = json_encode($value);
        } else {
            $this->attributes['meta'] = json_encode([]);
        }
    }

    public function event(){ 
        return $this->belongsTo(Event::class); 
    }
}
