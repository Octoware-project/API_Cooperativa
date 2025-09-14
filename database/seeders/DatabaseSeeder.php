<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
    // Llamar al seeder de facturas y pagos
    $this->call(Facturas_y_Pagos::class);
    $this->call(HorasMensualesSeeder::class);
    }
}
