<?php

namespace Database\Seeders;

use App\Models\User; // Importa el modelo User
use Illuminate\Support\Facades\Hash; // Importa la fachada Hash
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@correo.com',
            'password' => Hash::make('password123'),
            'active' => 1
        ]);
    }
}
