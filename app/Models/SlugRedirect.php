<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlugRedirect extends Model
{
    protected $fillable = ['old_path', 'new_path', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
