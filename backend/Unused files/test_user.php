<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

// 1. Check if we can load a user with specialty
try {
    echo "Attempting to load first user with specialty...\n";
    $user = User::with(['specialty', 'plan'])->first();
    if ($user) {
        echo "User found: " . $user->email . "\n";
        echo "Specialty: " . ($user->specialty ? $user->specialty->name : 'None') . "\n";
        echo "Plan: " . ($user->plan ? $user->plan->name : 'None') . "\n";
    } else {
        echo "No users found.\n";
    }
} catch (\Exception $e) {
    echo "ERROR loading user: " . $e->getMessage() . "\n";
}

// 2. Check if we can authenticate (hash check)
if ($user) {
    // This is hard to check without knowing password, but we can check if relations cause errors
    try {
        $json = $user->toJson();
        echo "User serialization successful.\n";
    } catch (\Exception $e) {
        echo "ERROR serializing user: " . $e->getMessage() . "\n";
    }
}
