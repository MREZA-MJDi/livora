<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSetting extends Model
{
    protected $fillable = [
        'brand_name',
        'logo_path',
        'phone',
        'email',
        'whatsapp_url',
        'telegram_url',
        'instagram_url',
        'eitaa_url',
        'bale_url',
        'address',
        'map_latitude',
        'map_longitude',
        'hours',
        'warranty_text',
        'shipping_text',
        'installation_text',
        'returns_text',
        'phone_label',
        'email_label',
        'support_label',
        'online_label',
        'support_description',
        'online_description',
        'faqs',
        'page_content',
        'is_active',
        'floating_actions_enabled',
    ];

    protected function casts(): array
    {
        return [
            'faqs' => 'array',
            'page_content' => 'array',
            'is_active' => 'boolean',
            'floating_actions_enabled' => 'boolean',
        ];
    }
}
