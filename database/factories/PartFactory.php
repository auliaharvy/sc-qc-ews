<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartFactory extends Factory
{
    protected $model = \App\Models\Part::class;

    public function definition()
    {
        return [
            'supplier_id' => Supplier::factory(),
            'part_number' => 'PART-' . $this->faker->unique()->bothify('??##'), // Format: PART-AB12
            'part_name' => $this->faker->randomElement([
                'Brake Pad',
                'Fuel Injector',
                'Spark Plug',
                'Air Filter',
                'Oil Filter',
                'Radiator',
                'Alternator',
                'Starter Motor',
                'Timing Belt',
                'Wheel Bearing'
            ])
        ];
    }

    public function forSupplier(Supplier $supplier)
    {
        return $this->state([
            'supplier_id' => $supplier->id
        ]);
    }
}
