<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Authorize private channel for a specific doctor
Broadcast::channel('doctor.{doctorId}', function ($user, $doctorId) {
    // Only the doctor can listen to their own channel, 
    // or staff belonging to this doctor can listen to reflect UI changes across the clinic.
    return (int) $user->id === (int) $doctorId || 
           ($user->role === 'staff' && (int) $user->doctor_id === (int) $doctorId);
});
