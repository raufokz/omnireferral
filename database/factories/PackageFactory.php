<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackageFactory extends Factory
{
    protected $model = Package::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'slug' => fake()->unique()->slug(2),
            'category' => 'lead',
            'billing_type' => 'monthly',
            'is_featured' => false,
            'is_active' => true,
            'one_time_price' => null,
            'monthly_price' => fake()->numberBetween(499, 2299),
            'hourly_price' => null,
            'features' => ['Feature A', 'Feature B'],
            'cta_label' => 'EXPLORE PLAN',
            'duration_days' => 30,
            'sort_order' => 0,
            'monthly_lead_quota' => 5,
            'lead_priority' => 1,
        ];
    }
}
