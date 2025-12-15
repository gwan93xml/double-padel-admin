<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            'app_name' => 'Admin Double Padel',
            'app_title' => 'Admin Double Padel',
            'company_name' => 'Double Padel',
            'logo' => null,
            'favicon' => null,
            'address' => null,
        ]);
    }
}
