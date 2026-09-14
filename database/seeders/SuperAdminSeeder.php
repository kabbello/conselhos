<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email    = env('SUPER_ADMIN_EMAIL');
        $password = env('SUPER_ADMIN_PASSWORD');

        if (app()->isProduction() && (empty($email) || empty($password))) {
            $this->command->error('Em produção, defina SUPER_ADMIN_EMAIL e SUPER_ADMIN_PASSWORD no .env antes de executar este seeder.');
            return;
        }

        // Valores padrão apenas para ambiente local/teste
        $email    = $email    ?? 'admin@conselhos.local';
        $password = $password ?? 'mudar-na-primeira-vez';

        if (strlen($password) < 12 && app()->isProduction()) {
            $this->command->error('SUPER_ADMIN_PASSWORD deve ter pelo menos 12 caracteres.');
            return;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name'                => env('SUPER_ADMIN_NAME', 'Super Admin'),
                'password'            => Hash::make($password),
                'email_verified_at'   => now(),
                'must_reset_password' => true, // sempre forçar troca na primeira entrada
            ]
        );

        $user->assignRole('super_admin');

        $this->command->info("Super admin: {$user->email}");
        $this->command->warn('Acesso inicial exigirá troca de senha (must_reset_password = true).');
    }
}
