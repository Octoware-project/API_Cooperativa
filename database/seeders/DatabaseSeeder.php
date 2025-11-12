<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $backofficeSeederPath = base_path('../Backoffice/database/seeders');
        
        if (file_exists($backofficeSeederPath)) {
            $this->command->info('Ejecutando seeders del Backoffice...');
            
            require_once $backofficeSeederPath . '/UserAdminSeeder.php';
            require_once $backofficeSeederPath . '/JuntasAsambleaSeeder.php';
            require_once $backofficeSeederPath . '/Facturas_y_Pagos.php';
            require_once $backofficeSeederPath . '/PersonaEstadoSeeder.php';
            require_once $backofficeSeederPath . '/UnidadHabitacionalSeeder.php';
            require_once $backofficeSeederPath . '/PlanTrabajoSeeder.php';
            require_once $backofficeSeederPath . '/HorasMensualesSeeder.php';
            
            $this->call([
                \Database\Seeders\UserAdminSeeder::class,
                \Database\Seeders\JuntasAsambleaSeeder::class,
                \Database\Seeders\Facturas_y_Pagos::class,
                \Database\Seeders\PersonaEstadoSeeder::class,
                \Database\Seeders\UnidadHabitacionalSeeder::class,
                \Database\Seeders\PlanTrabajoSeeder::class,
                \Database\Seeders\HorasMensualesSeeder::class,
            ]);
        } else {
            $this->command->warn('No se encontraron los seeders del Backoffice');
        }
    }
}
