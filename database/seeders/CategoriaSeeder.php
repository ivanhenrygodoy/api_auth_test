<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('ctl_categoria_producto')->insert([
            [
                'nombre' => 'Alimento Perecedero grado A',
            ],
            [
                'nombre' => 'Alimento Perecedero grado B',
            ],
            [
                'nombre' => 'Alimento Perecedero grado C',
            ],
            [
                'nombre' => 'Material reciclable biodegradable',
            ],
            [
                'nombre' => 'Material reciclable reforzado',
            ],

        ]);
    }
}
