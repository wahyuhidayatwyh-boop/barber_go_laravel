<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika tidak mengikuti aturan jamak (optional)
    protected $table = 'services';

    /**
     * Kolom yang boleh diisi (Mass Assignment).
     * Ini harus sesuai dengan kolom yang ada di migration Anda.
     */
    protected $fillable = [
        'name',
        'price',
        'description',
        'image_url',
    ];

    /**
     * Cast harga ke integer agar saat dikirim ke Flutter
     * datanya dipastikan berupa angka, bukan string.
     */
    protected $casts = [
        'price' => 'integer',
    ];
}