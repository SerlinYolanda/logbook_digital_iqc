<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Fresh Administrator Account
        User::create([
            'name' => 'Administrator IQC',
            'nik' => 'ADM-001',
            'role' => 'admin',
            'shift' => '1',
            'email' => 'admin@iqc.local',
            'password' => Hash::make('password123'),
        ]);
    }
}
