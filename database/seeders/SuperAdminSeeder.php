<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => env('SUPER_ADMIN_EMAIL', 'admin@conselhos.local')],
            [
                'name'                => 'Super Admin',
                'password'            => Hash::make(env('SUPER_ADMIN_PASSWORD', 'mudar-na-primeira-vez')),
                'email_verified_at'   => now(),
                'must_reset_password' => false,
            ]
        );

        $user->assignRole('super_admin');

        $this->command->info("Super admin criado: {$user->email}");
        $this->command->warn('Altere a senha imediatamente após o primeiro login!');
    }
}
