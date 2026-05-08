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
            // Sync image_path and image_url
            if ($barber->isDirty('image_path') && !$barber->isDirty('image_url')) {
                $path = $barber->image_path;
                if ($path && !filter_var($path, FILTER_VALIDATE_URL)) {
                    $barber->image_url = url($path);
                } else {
                    $barber->image_url = $path;
                }
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
