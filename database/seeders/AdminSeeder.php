<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::create([
            'name'           => 'School Administrator',
            'email'          => 'admin@sdnhs.edu.ph',
            'password'       => Hash::make('Admin@SDNHS2025!'),
            'is_super_admin' => true,
        ]);
    }
}