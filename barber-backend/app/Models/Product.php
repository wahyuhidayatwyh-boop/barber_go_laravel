<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
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
        'description',
        'image_path',
        'image_url',
        'category',
        'stock_quantity',
        'is_available',
        'status', // active, inactive
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saving(function ($product) {
            // Sync image_path and image_url
            if ($product->isDirty('image_path') && !$product->isDirty('image_url')) {
                $path = $product->image_path;
                if ($path && !filter_var($path, FILTER_VALIDATE_URL)) {
                    // It's a local path, convert to full URL for image_url
                    $product->image_url = url($path);
                } else {
                    // It's already a URL or empty
                    $product->image_url = $path;
                }
            } elseif ($product->isDirty('image_url') && !$product->isDirty('image_path')) {
                $product->image_path = $product->image_url;
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
            'stock_quantity' => 'integer',
        ];
    }
}
