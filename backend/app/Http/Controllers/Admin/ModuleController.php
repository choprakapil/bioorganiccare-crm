<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;

class ModuleController extends Controller
{
    public function index()
    {
        return response()->json(
            Module::select('id','key','name','description','is_active')
                ->orderBy('name')
                ->get()
        );
    }
}
