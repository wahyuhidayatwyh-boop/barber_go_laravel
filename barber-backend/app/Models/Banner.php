<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image_url',
        'image_path',
        'is_active',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saving(function ($banner) {
            // Sync image_path and image_url - store relative paths only
            if ($banner->isDirty('image_path') && !$banner->isDirty('image_url')) {
                $banner->image_url = $banner->image_path;
            } elseif ($banner->isDirty('image_url') && !$banner->isDirty('image_path')) {
                $banner->image_path = $banner->image_url;
            }
        });
    }

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
