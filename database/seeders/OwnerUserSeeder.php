<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class OwnerUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = env('OWNER_NAME');
        $email = env('OWNER_EMAIL');
        $password = env('OWNER_PASSWORD');

        if (! $name || ! $email || ! $password) {
            throw new RuntimeException(
                'Set OWNER_NAME, OWNER_EMAIL and OWNER_PASSWORD in .env first.'
            );
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => 'owner',
            ]
        );

        if ($user->role !== 'owner') {
            throw new RuntimeException(
                'This email already belongs to a non-owner account. Choose a different OWNER_EMAIL.'
            );
        }
    }
}
