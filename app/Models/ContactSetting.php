<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSetting extends Model
{
    protected $fillable = [
        'phone',
        'email',
        'address',
        'hours',
        'phone_label',
        'email_label',
        'support_label',
        'online_label',
        'support_description',
        'online_description',
        'faqs',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'faqs' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
