<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Skill extends Model implements HasMedia
{
    use InteractsWithMedia, HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'tags' => 'array',
    ];
}
