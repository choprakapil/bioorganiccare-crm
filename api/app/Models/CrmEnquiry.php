<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmEnquiry extends Model
{
    /** @use HasFactory<\Database\Factories\CrmEnquiryFactory> */
    use HasFactory;

    protected $fillable = [
        'name', 'clinic_name', 'phone', 'whatsapp', 'city', 'practice_type', 
        'message', 'ip_address', 'user_agent', 'browser_name', 'os_name', 
        'device_type', 'referrer'
    ];
}
