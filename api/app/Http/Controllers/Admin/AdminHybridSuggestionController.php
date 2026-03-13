<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Support\Services\ServiceNormalizer;

class AdminHybridSuggestionController extends Controller
{
    public function services()
    {
        $services = DB::table('local_services as ls')
            ->join('users as u', 'u.id', '=', 'ls.doctor_id')
            ->select(
                'ls.id',
                'ls.item_name',
                'ls.normalized_name',
                'ls.type',
                'ls.default_fee',
                'ls.doctor_id',
                'u.name as doctor_name',
                'ls.specialty_id',
                'ls.created_at',
                'ls.is_promoted'
            )
            ->where('ls.is_promoted', false)
            ->whereNull('ls.deleted_at')
            ->get();

        return response()->json($services->map(fn($s) => ServiceNormalizer::normalize($s, 'local')));
    }

    public function medicines()
    {
        $medicines = DB::table('local_medicines as lm')
            ->join('users as u', 'u.id', '=', 'lm.doctor_id')
            ->select(
                'lm.id',
                'lm.item_name',
                'lm.normalized_name',
                'lm.buy_price',
                'lm.sell_price',
                'lm.doctor_id',
                'u.name as doctor_name',
                'lm.specialty_id',
                'lm.created_at',
                'lm.is_promoted'
            )
            ->where('lm.is_promoted', false)
            ->whereNull('lm.deleted_at')
            ->get();

        return response()->json($medicines);
    }

    public function masterMedicines()
    {
        $masters = DB::table('master_medicines')
            ->whereNull('deleted_at')
            ->get();

        return response()->json($masters);
    }
}
