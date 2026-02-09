<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('admin123');

        // --- 1. SUPER ADMIN ---
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@booknstay.com',
            'password' => $password,
            'role' => 'super_admin',
            'status' => 'active',
        ]);
    }
}
