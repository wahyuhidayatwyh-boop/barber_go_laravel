<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barber extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'specialty',
        'rating',
        'image_path',
        'image_url',
        'status', // active, inactive
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saving(function ($barber) {
            // Sync image_path and image_url - store relative paths only
            if ($barber->isDirty('image_path') && !$barber->isDirty('image_url')) {
                // Store relative path in image_url too (will be resolved at API response time)
                $barber->image_url = $barber->image_path;
            } elseif ($barber->isDirty('image_url') && !$barber->isDirty('image_path')) {
                $barber->image_path = $barber->image_url;
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
            'rating' => 'decimal:2',
        ];
    }
}
