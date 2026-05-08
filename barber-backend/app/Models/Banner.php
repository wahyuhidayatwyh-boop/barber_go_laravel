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
            // Sync image_path and image_url
            if ($banner->isDirty('image_path') && !$banner->isDirty('image_url')) {
                $path = $banner->image_path;
                if ($path && !filter_var($path, FILTER_VALIDATE_URL)) {
                    $banner->image_url = url($path);
                } else {
                    $banner->image_url = $path;
                }
            } elseif ($banner->isDirty('image_url') && !$banner->isDirty('image_path')) {
                $banner->image_path = $banner->image_url;
            }
        });
    }
}
