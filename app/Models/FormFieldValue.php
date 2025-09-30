<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormFieldValue extends Model
{
    protected $fillable = [
        'registration_id','field_id',
        'value','value_json','value_number',
    ];

    protected $casts = [
        'value_json' => 'array', 
        'value_number' => 'decimal:6'
    ];

    public function registration(){ 
        return $this->belongsTo(Registration::class); 
    }
    
    public function field(){ 
        return $this->belongsTo(FormField::class, 'field_id'); 
    }
}
