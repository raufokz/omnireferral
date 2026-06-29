<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'lead_number' => Lead::generateLeadNumber(),
            'intent' => fake()->randomElement(['buyer', 'seller']),
            'package_type' => fake()->randomElement(['quick', 'power', 'prime']),
            'status' => 'new',
            'source' => 'seed',
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('##########'),
            'zip_code' => fake()->postcode(),
        ];
    }
}
