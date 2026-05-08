<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'price',
        'duration',
        'description',
        'image_url',
        'image_path',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saving(function ($service) {
            // Sync image_path and image_url
            if ($service->isDirty('image_path') && !$service->isDirty('image_url')) {
                $path = $service->image_path;
                if ($path && !filter_var($path, FILTER_VALIDATE_URL)) {
                    $service->image_url = url($path);
                } else {
                    $service->image_url = $path;
                }
            } elseif ($service->isDirty('image_url') && !$service->isDirty('image_path')) {
                $service->image_path = $service->image_url;
            }
        });
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'duration' => 'integer',
        ];
    }
}
