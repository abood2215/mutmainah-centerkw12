<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['username' => '228'],
            [
                'name'     => 'المدير',
                'username' => '228',
                'password' => bcrypt('2296669'),
                'role'     => 'admin',
            ]
        );
    }
}
