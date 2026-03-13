<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Specialty;
use App\Models\SubscriptionPlan;
use App\Models\ClinicalCatalog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Subscription Plans
        $basic = SubscriptionPlan::create([
            'name' => 'Basic',
            'price' => 0,
            'max_patients' => 10,
            'max_appointments_monthly' => 30,
            'features' => json_encode([
                'modules' => [
                    'patients' => true, 
                    'billing' => true, 
                    'expenses' => false, 
                    'inventory' => false,
                    'pharmacy' => false,
                    'growth_insights' => true
                ],
                'limits' => ['max_staff' => 2]
            ])
        ]);

        $pro = SubscriptionPlan::create([
            'name' => 'Pro',
            'price' => 2500,
            'max_patients' => -1, // Unlimited
            'max_appointments_monthly' => -1, // Unlimited
            'features' => json_encode([
                'modules' => [
                    'patients' => true, 
                    'billing' => true, 
                    'expenses' => true, 
                    'inventory' => true,
                    'pharmacy' => true,
                    'growth_insights' => true
                ],
                'limits' => ['max_staff' => 5]
            ])
        ]);

        // 2. Specialties
        $dental = Specialty::create(['name' => 'Dental', 'has_teeth_chart' => true]);
        $general = Specialty::create(['name' => 'General Physician', 'has_teeth_chart' => false]);

        // 3. Clinical Catalog (Dental)
        $dentalItems = [
            ['name' => 'Routine Checkup', 'type' => 'Treatment', 'fee' => 500],
            ['name' => 'Scaling & Polishing', 'type' => 'Treatment', 'fee' => 1500],
            ['name' => 'Root Canal Treatment', 'type' => 'Treatment', 'fee' => 4500],
            ['name' => 'Amoxicillin 500mg', 'type' => 'Medicine', 'fee' => 20],
            ['name' => 'Paracetamol 650mg', 'type' => 'Medicine', 'fee' => 10],
        ];

        foreach ($dentalItems as $item) {
            ClinicalCatalog::create([
                'specialty_id' => $dental->id,
                'item_name' => $item['name'],
                'type' => $item['type'],
                'default_fee' => $item['fee']
            ]);
        }

        // 4. Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'choprakapil77@gmail.com',
            'password' => Hash::make('Kapil@123'),
            'role' => 'super_admin'
        ]);

        // 5. Test Doctor
        User::create([
            'name' => 'Dr. Smith',
            'email' => 'smith@aura.com',
            'password' => Hash::make('doctor123'),
            'role' => 'doctor',
            'specialty_id' => $dental->id,
            'plan_id' => $pro->id,
            'phone' => '9876543210'
        ]);
    }
}
