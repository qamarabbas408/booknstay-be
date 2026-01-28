<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        //
        User::create([
        'name' => 'Super Admin',
        'email' => 'admin@marketplace.com',
        'password' => Hash::make('password123'), // Use a strong password!
        'role' => 'super_admin',
    ]);
    }
}
