<?php

namespace Database\Seeders;

use App\Models\User; // Importa el modelo User
use Illuminate\Support\Facades\Hash; // Importa la fachada Hash
use Illuminate\Database\Seeder;

class UserSeeder2 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'User',
            'email' => 'user@correo.com',
            'password' => Hash::make('user123'),
            'active' => 1
        ]);
    }
}
