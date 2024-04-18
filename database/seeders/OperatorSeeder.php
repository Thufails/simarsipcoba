<?php

namespace Database\Seeders;

use App\Models\Operator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OperatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $operators = [
            [
                'NAMA_OPERATOR' => 'Rafi',
                'email' => 'operator1@example.com',
                'password' => Hash::make('password123'),
                'ID_AKSES' => 3,
            ],
            [
                'NAMA_OPERATOR' => 'Idris',
                'email' => 'operator2@example.com',
                'password' => Hash::make('password456'),
                'ID_AKSES' => 3,
            ],
        ];

        // Memasukkan data operator ke dalam database
        foreach ($operators as $operator) {
            Operator::create($operator);
        }
    }
}
