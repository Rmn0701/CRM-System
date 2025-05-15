<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function contacts()
    {
        return $this->belongsToMany(Contact::class)->withPivot('value');
    }

    public function values()
    {
        return $this->hasMany(ContactCustomFieldValue::class);
    }

}

