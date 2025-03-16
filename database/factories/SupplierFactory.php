<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => 'PT. ' . $this->faker->company(),
            'code' => 'SUP-' . $this->faker->unique()->numerify('####'),
            'email' => $this->faker->companyEmail(),
            'pic' => $this->faker->name(),
        ];
    }
}
