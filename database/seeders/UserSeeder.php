<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeded demo accounts for the support/admin dashboard. There is no public
 * self-registration; staff accounts only ever come from seeding or (in a real
 * deployment) an internal admin action, never a customer-facing form.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@workfund.test'],
            [
                'name' => 'Ade Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'support@workfund.test'],
            [
                'name' => 'Sade Support',
                'password' => Hash::make('password'),
                'role' => 'support',
            ],
        );
    }
}
