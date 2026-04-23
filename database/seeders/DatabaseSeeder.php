<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminAccounts = [
            [
                'name' => 'ETB Admin',
                'email' => 'adminETB@admin.com',
                'password' => 'aminetb!2025',
            ],
        ];

        foreach ($adminAccounts as $admin) {
            User::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'password' => Hash::make($admin['password']),
                    'role' => User::ROLE_ADMIN,
                ]
            );
        }
    }
}
