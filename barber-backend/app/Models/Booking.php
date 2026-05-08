<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'service_id',
        'barber_id',
        'booking_date',
        'booking_time',
        'total_price',
        'duration',
        'status', // pending, confirmed, completed, cancelled
        'payment_method',
        'payment_status', // unpaid, paid
        'booking_id', // unique identifier shown to user
        'phone', // customer phone number for WhatsApp notifications
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_price' => 'integer',
            'duration' => 'integer',
            'booking_date' => 'date',
        ];
    }

    /**
     * Relationship with User model
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with Service model
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Relationship with Barber model
     */
    public function barber(): BelongsTo
    {
        return $this->belongsTo(Barber::class);
    }
}
