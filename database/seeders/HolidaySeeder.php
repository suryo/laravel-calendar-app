<?php

namespace Database\Seeders;
use App\Models\Holiday;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Holiday::insert([
            ['holiday_date' => '2025-06-01', 'description' => 'Hari Lahir Pancasila'],
            ['holiday_date' => '2025-06-17', 'description' => 'Hari Raya Idul Adha'],
            ['holiday_date' => '2025-06-30', 'description' => 'Cuti Bersama'],
        ]);
    }
}
