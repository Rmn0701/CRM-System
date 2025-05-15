<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'profile_image',
        'additional_file',
        'is_merged',
        'merged_into'
    ];

    public function mergedInto()
    {
        return $this->belongsTo(Contact::class, 'merged_into');
    }

    public function mergedContacts()
    {
        return $this->hasMany(Contact::class, 'merged_into');
    }

    public function scopeActive($query)
    {
        return $query->where('is_merged', false);
    }

    public function customFields()
    {
        return $this->belongsToMany(CustomField::class, 'contact_custom_field_values')
                    ->withPivot('value')
                    ->withTimestamps();
    }


    public function customFieldValues()
    {
        return $this->hasMany(ContactCustomFieldValue::class);
    }

    
    public function customFieldsWithValues()
    {
        return $this->hasMany(ContactCustomFieldValue::class)->with('customField');
    }



}
