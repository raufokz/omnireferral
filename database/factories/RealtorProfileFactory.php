<?php

namespace Database\Factories;

use App\Models\RealtorProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class RealtorProfileFactory extends Factory
{
    protected $model = RealtorProfile::class;

    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(),
            'service_city' => fake()->city(),
            'service_state' => fake()->stateAbbr(),
            'service_zip_code' => fake()->postcode(),
            'license_number' => fake()->numerify('########'),
            'bio' => fake()->sentence(),
        ];
    }
}
