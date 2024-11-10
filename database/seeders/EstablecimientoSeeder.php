<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EstablecimientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('ctl_establecimiento_origen')->insert([
            [
                'nombre' => 'Central de Abastos San Salvador',
            ],
            [
                'nombre' => 'Central de Abastos Santa Ana',
            ],
            [
                'nombre' => 'Central de Abastos San Miguel',
            ],
            [
                'nombre' => 'Mercado Central Centro Historico',
            ],
            [
                'nombre' => 'Mercado Municipal de San Vicente',
            ],
            [
                'nombre' => 'Mercado de Comida de San Pedro',
            ],
            [
                'nombre' => 'Mercado de Artesanías de La Libertad',
            ],
            [
                'nombre' => 'Mercado El Progreso',
            ],

        ]);
    }
}
