<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomepageHeroSetting extends Model
{
    protected $fillable = ['background_path'];

    public function getBackgroundUrlAttribute(): ?string
    {
        if (! $this->background_path) {
            return null;
        }

        return Storage::disk('public')->url($this->background_path);
    }
}
