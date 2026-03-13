<?php

namespace App\Http\Controllers;

use App\Models\CrmEnquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LandingEnquiryController extends Controller
{
    /**
     * Submit an enquiry from the landing page (Public).
     */
    public function store(Request $request)
    {
        try {
            $enquiry = CrmEnquiry::create([
                'name' => $request->input('name', 'N/A'),
                'clinic_name' => $request->input('clinic_name', 'N/A'),
                'phone' => $request->input('phone', 'N/A'),
                'whatsapp' => $request->input('whatsapp'),
                'city' => $request->input('city', 'N/A'),
                'practice_type' => $request->input('practice_type'),
                'message' => $request->input('message'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referrer' => $request->header('referer'),
            ]);

            return response()->json([
                'success' => true,
                'id' => $enquiry->id
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function index()
    {
        try {
            $enquiries = CrmEnquiry::orderBy('created_at', 'desc')->get();
            return response()->json(['data' => $enquiries]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Private helper to parse User Agent.
     */
    private function parseUserAgent($ua)
    {
        $browser = "Unknown";
        $platform = "Unknown";
        $device = "Desktop";

        // Platform detection
        if (preg_match('/android/i', $ua)) { $platform = 'Android'; $device = 'Mobile'; }
        elseif (preg_match('/iphone/i', $ua)) { $platform = 'iOS'; $device = 'Mobile'; }
        elseif (preg_match('/ipad/i', $ua)) { $platform = 'iOS'; $device = 'Tablet'; }
        elseif (preg_match('/linux/i', $ua)) { $platform = 'Linux'; }
        elseif (preg_match('/macintosh|mac os x/i', $ua)) { $platform = 'Mac'; }
        elseif (preg_match('/windows|win32/i', $ua)) { $platform = 'Windows'; }

        // Browser detection
        if(preg_match('/MSIE/i',$ua) && !preg_match('/Opera/i',$ua)) { $browser = 'Internet Explorer'; }
        elseif(preg_match('/Firefox/i',$ua)) { $browser = 'Firefox'; }
        elseif(preg_match('/Chrome/i',$ua)) { $browser = 'Chrome'; }
        elseif(preg_match('/Safari/i',$ua)) { $browser = 'Safari'; }
        elseif(preg_match('/Opera/i',$ua)) { $browser = 'Opera'; }

        return [
            'browser' => $browser,
            'platform' => $platform,
            'device' => $device
        ];
    }
}
