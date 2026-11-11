<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContactSetting extends Model
{
    protected $fillable = [
        'brand_name', 'brand_description',
        'enamad_url', 'samandehi_url', 'footer_menu', 'payment_methods', 'copyright_text', 'logo_media_id',
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


    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo_media_id && $this->logoMedia && filled($this->logoMedia->url)) {
            return $this->logoMedia->url;
        }

        if (! $this->logo_path) {
            return null;
        }

        if (Str::startsWith($this->logo_path, ['http://', 'https://', '//'])) {
            return $this->logo_path;
        }

        $path = ltrim($this->logo_path, '/');

        return Storage::disk('public')->exists($path) ? Storage::disk('public')->url($path) : null;
    }

    public function logoMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'logo_media_id');
    }

    public function hasMapLocation(): bool
    {
        if (! is_numeric($this->map_latitude) || ! is_numeric($this->map_longitude)) {
            return false;
        }

        $latitude = (float) $this->map_latitude;
        $longitude = (float) $this->map_longitude;

        return is_finite($latitude)
            && is_finite($longitude)
            && $latitude >= -90
            && $latitude <= 90
            && $longitude >= -180
            && $longitude <= 180;
    }

    public function googleMapsEmbedUrl(): ?string
    {
        if (! $this->hasMapLocation()) {
            return null;
        }

        $coordinates = sprintf('%.7F,%.7F', (float) $this->map_latitude, (float) $this->map_longitude);

        return 'https://maps.google.com/maps?' . http_build_query([
            'q' => $coordinates,
            'z' => 15,
            'output' => 'embed',
        ]);
    }

    public function googleMapsDirectionsUrl(): ?string
    {
        if (! $this->hasMapLocation()) {
            return null;
        }

        $coordinates = sprintf('%.7F,%.7F', (float) $this->map_latitude, (float) $this->map_longitude);

        return 'https://www.google.com/maps/dir/?' . http_build_query([
            'api' => 1,
            'destination' => $coordinates,
        ]);
    }

    public function googleMapsSearchUrl(): ?string
    {
        if ($this->hasMapLocation()) {
            return $this->googleMapsDirectionsUrl();
        }

        $address = trim((string) $this->address);

        return $address !== ''
            ? 'https://www.google.com/maps/search/?' . http_build_query(['api' => 1, 'query' => $address])
            : null;
    }

    protected function casts(): array
    {
        return [
            'faqs' => 'array',
            'footer_menu' => 'array',
            'payment_methods' => 'array',
            'page_content' => 'array',
            'is_active' => 'boolean',
            'floating_actions_enabled' => 'boolean',
        ];
    }
}
