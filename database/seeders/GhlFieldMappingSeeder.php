<?php

namespace Database\Seeders;

use App\Models\GhlFieldMapping;
use Illuminate\Database\Seeder;

class GhlFieldMappingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // Users table
            ['ghl_field' => 'name',         'db_table' => 'users', 'db_column' => 'name',           'label' => 'Full Name',        'sort_order' => 10],
            ['ghl_field' => 'first_name',   'db_table' => 'users', 'db_column' => 'name',           'label' => 'First Name → Name','sort_order' => 11],
            ['ghl_field' => 'email',        'db_table' => 'users', 'db_column' => 'email',          'label' => 'Email Address',    'sort_order' => 12],
            ['ghl_field' => 'phone',        'db_table' => 'users', 'db_column' => 'phone',          'label' => 'Phone Number',     'sort_order' => 13],
            ['ghl_field' => 'address',      'db_table' => 'users', 'db_column' => 'address_line_1', 'label' => 'Street Address',   'sort_order' => 14],
            ['ghl_field' => 'city',         'db_table' => 'users', 'db_column' => 'city',           'label' => 'City',             'sort_order' => 15],
            ['ghl_field' => 'state',        'db_table' => 'users', 'db_column' => 'state',          'label' => 'State',            'sort_order' => 16],
            ['ghl_field' => 'zip_code',     'db_table' => 'users', 'db_column' => 'zip_code',       'label' => 'Zip Code',         'sort_order' => 17],
            ['ghl_field' => 'role',         'db_table' => 'users', 'db_column' => 'role',           'label' => 'User Role',        'sort_order' => 18],

            // Realtor profiles
            ['ghl_field' => 'brokerage_name',  'db_table' => 'realtor_profiles', 'db_column' => 'brokerage_name',  'label' => 'Brokerage Name',    'sort_order' => 30],
            ['ghl_field' => 'license_number',  'db_table' => 'realtor_profiles', 'db_column' => 'license_number',  'label' => 'License Number',    'sort_order' => 31],
            ['ghl_field' => 'bio',             'db_table' => 'realtor_profiles', 'db_column' => 'bio',             'label' => 'Agent Bio',         'sort_order' => 32],
            ['ghl_field' => 'specialties',     'db_table' => 'realtor_profiles', 'db_column' => 'specialties',     'label' => 'Specialties',       'sort_order' => 33],
            ['ghl_field' => 'service_city',    'db_table' => 'realtor_profiles', 'db_column' => 'service_city',    'label' => 'Service City',      'sort_order' => 34],
            ['ghl_field' => 'service_state',   'db_table' => 'realtor_profiles', 'db_column' => 'service_state',   'label' => 'Service State',     'sort_order' => 35],

            // Buyer profiles
            ['ghl_field' => 'preferred_location', 'db_table' => 'buyer_profiles', 'db_column' => 'preferred_locations', 'label' => 'Preferred Location', 'sort_order' => 50],
            ['ghl_field' => 'budget_min',         'db_table' => 'buyer_profiles', 'db_column' => 'budget_min',          'label' => 'Minimum Budget',     'sort_order' => 51],
            ['ghl_field' => 'budget_max',         'db_table' => 'buyer_profiles', 'db_column' => 'budget_max',          'label' => 'Maximum Budget',     'sort_order' => 52],
            ['ghl_field' => 'financing_status',   'db_table' => 'buyer_profiles', 'db_column' => 'financing_status',    'label' => 'Financing Status',   'sort_order' => 53],
            ['ghl_field' => 'timeline',           'db_table' => 'buyer_profiles', 'db_column' => 'timeline',            'label' => 'Purchase Timeline',  'sort_order' => 54],
            ['ghl_field' => 'notes',              'db_table' => 'buyer_profiles', 'db_column' => 'notes',               'label' => 'Buyer Notes',        'sort_order' => 55],
        ];

        foreach ($defaults as $mapping) {
            GhlFieldMapping::updateOrCreate(
                [
                    'ghl_field' => $mapping['ghl_field'],
                    'db_table'  => $mapping['db_table'],
                    'db_column' => $mapping['db_column'],
                ],
                [
                    'label'      => $mapping['label'],
                    'is_active'  => true,
                    'sort_order' => $mapping['sort_order'],
                ]
            );
        }
    }
}
