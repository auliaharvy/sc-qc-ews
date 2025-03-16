<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\Part;
use App\Models\NgType;
use App\Models\DailyChecksheet;
use Faker\Factory as Faker;

class InitialSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');

        // Seed Suppliers
        Supplier::factory()->count(10)->create()->each(function ($supplier) use ($faker) {
            // Seed Parts for each Supplier
            Part::factory()->count(2)->create([
                'supplier_id' => $supplier->id,
                'part_number' => 'PART-' . $faker->unique()->bothify('??##'),
                'part_name' => $faker->randomElement([
                    'Brake Pad',
                    'Fuel Injector',
                    'Spark Plug',
                    'Air Filter',
                    'Oil Filter'
                ])
            ]);
        });

        // Seed NG Types
        $ngTypes = [
            'Cacat Permukaan',
            'Dimensi Tidak Sesuai',
            'Karat',
            'Kebocoran',
            'Warna Tidak Sesuai'
        ];

        foreach ($ngTypes as $type) {
            NgType::create(['name' => $type]);
        }

        // Seed Daily Checksheets
        Part::all()->each(function ($part) use ($faker) {
            for ($i = 0; $i < 5; $i++) {
                $totalProduced = $faker->numberBetween(100, 500);
                $totalOk = $totalProduced - $faker->numberBetween(5, 50);
                $totalNg = $totalProduced - $totalOk;

                $checksheet = DailyChecksheet::create([
                    'part_id' => $part->id,
                    'supplier_id' => $part->supplier_id,
                    'production_date' => $faker->dateTimeBetween('-1 month', 'now'),
                    'total_produced' => $totalProduced,
                    'total_ok' => $totalOk,
                    'total_ng' => $totalNg
                ]);

                // Attach NG Types
                $ngTypes = NgType::inRandomOrder()
                    ->limit($faker->numberBetween(1, 3))
                    ->get()
                    ->mapWithKeys(function ($ngType) use ($faker) {
                        return [$ngType->id => ['quantity' => $faker->numberBetween(1, 20)]];
                    });

                $checksheet->ngTypes()->attach($ngTypes);
            }
        });
    }
}
