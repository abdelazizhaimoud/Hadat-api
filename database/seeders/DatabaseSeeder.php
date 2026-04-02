<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        User::create([
            'name' => "Abdelaziz Haimoud",
            'email' => "abdelazizhaimoud@outlook.com",
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'bio' => "he/him developer",
            'avatar' => "/avatar.png",
            'remember_token' => Str::random(10),
        ]);
        User::factory(10)->create();
    }
}
