<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageLeadQuotaSeeder extends Seeder
{
    public function run(): void
    {
        $quotas = [
            'starter-leads'  => ['monthly_lead_quota' => 5,  'lead_priority' => 1],
            'growth-leads'   => ['monthly_lead_quota' => 15, 'lead_priority' => 2],
            'elite-leads'    => ['monthly_lead_quota' => 35, 'lead_priority' => 3],
        ];

        foreach ($quotas as $slug => $data) {
            Package::where('slug', $slug)->update($data);
        }
    }
}
