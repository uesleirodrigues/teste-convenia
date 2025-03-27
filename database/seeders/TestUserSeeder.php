<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Ueslei',
            'email' => 'uesleibarros@hotmail.com',
            'password' => Hash::make('senha123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
