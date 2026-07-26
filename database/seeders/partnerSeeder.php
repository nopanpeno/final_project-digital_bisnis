<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\partners;
use Faker\Factory as Faker;

class partnerSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 1; $i <= 5; $i++) {
            partners::create([
                'name' => $faker->company,
                'logo_url' => 'https://placehold.co/200x200',
            ]);
        }
    }
}