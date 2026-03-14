<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Patient;
use App\Models\Invoice;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Enums\InvoiceStatus;

class DashboardController extends Controller
{
    public function stats()
    {
        $today = Carbon::today();
        
        $stats = [
            'total_doctors' => User::where('role', 'doctor')->count(),
            'active_doctors' => User::where('role', 'doctor')->where('is_active', true)->count(),
            'total_patients' => Patient::count(),
            'total_appointments' => Appointment::count(),
            'total_revenue' => Invoice::where('status', InvoiceStatus::PAID)->sum('total_amount'),
            'monthly_revenue' => Invoice::where('status', InvoiceStatus::PAID)
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('total_amount'),
            'new_doctors_this_month' => User::where('role', 'doctor')
                ->where('created_at', '>=', Carbon::now()->startOfMonth())
                ->count(),
        ];

        return response()->json($stats);
    }
}
