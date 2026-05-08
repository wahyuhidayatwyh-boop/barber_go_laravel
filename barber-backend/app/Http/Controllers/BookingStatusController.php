<?php

namespace App\Http\Controllers;

use App\Events\BookingStatusUpdated;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingStatusController extends Controller
{
    public function __construct()
    {
        // Middleware 'isAdmin' is applied at the route level in web.php
        // All methods in this controller require admin access
    }

    /**
     * Update booking status to 'confirmed' (Check-in)
     */
    public function checkIn($id)
    {
        $booking = Booking::where('booking_id', $id)->firstOrFail();
        $booking->update(['status' => 'confirmed']);
        
        // Broadcast the status update
        event(new BookingStatusUpdated($booking));
        
        return response()->json([
            'success' => true,
            'booking' => $booking,
            'message' => 'Check-in berhasil!'
        ]);
    }

    /**
     * Update booking status to 'in_progress' (Cukur/In progress)
     */
    public function startCukur($id)
    {
        $booking = Booking::where('booking_id', $id)->firstOrFail();
        $booking->update(['status' => 'in_progress']);
        
        // Broadcast the status update
        event(new BookingStatusUpdated($booking));
        
        return response()->json([
            'success' => true,
            'booking' => $booking,
            'message' => 'Status cukur dimulai!'
        ]);
    }

    /**
     * Update booking status to 'completed' (Selesai)
     */
    public function complete($id)
    {
        $booking = Booking::where('booking_id', $id)->firstOrFail();
        $booking->update(['status' => 'completed']);
        
        // Broadcast the status update
        event(new BookingStatusUpdated($booking));
        
        return response()->json([
            'success' => true,
            'booking' => $booking,
            'message' => 'Booking selesai!'
        ]);
    }

    /**
     * Update booking status to 'cancelled' (Cancel)
     */
    public function cancel($id)
    {
        $booking = Booking::where('booking_id', $id)->firstOrFail();
        $booking->update(['status' => 'cancelled']);
        
        // Broadcast the status update
        event(new BookingStatusUpdated($booking));
        
        return response()->json([
            'success' => true,
            'booking' => $booking,
            'message' => 'Booking dibatalkan!'
        ]);
    }

    /**
     * Update booking status to any valid status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,in_progress,completed,cancelled'
        ]);

        $booking = Booking::where('booking_id', $id)->firstOrFail();
        $booking->update(['status' => $request->status]);

        // Broadcast the status update
        event(new BookingStatusUpdated($booking));

        return response()->json([
            'success' => true,
            'booking' => $booking,
            'message' => 'Status berhasil diperbarui!'
        ]);
    }
}
