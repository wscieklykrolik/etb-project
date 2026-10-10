<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SetupAdmin extends Command
{
    protected $signature = 'etb:admin {--reset-password : Ustaw nowe hasło istniejącego administratora}';

    protected $description = 'Utwórz administratora admin@etb.pl lub ustaw jego nowe hasło';

    public function handle(): int
    {
        $email = config('accounts.admin_login');
        $user = User::where('email', $email)->first();
        if ($user && (! $user->isAdmin() || ! $this->option('reset-password'))) {
            $this->error('Konto już istnieje. Reset administratora wymaga opcji --reset-password. Konto innej roli nie zostanie zmienione.');

            return self::FAILURE;
        }

        $password = $this->secret('Hasło administratora (15–128 znaków)');
        if (! is_string($password) || mb_strlen($password) < 15 || mb_strlen($password) > 128) {
            $this->error('Hasło musi mieć od 15 do 128 znaków. Nic nie zmieniono.');

            return self::FAILURE;
        }
        if ($password !== $this->secret('Powtórz hasło administratora')) {
            $this->error('Hasła nie są identyczne. Nic nie zmieniono.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($user, $email, $password) {
            $admin = $user ?? new User(['name' => 'Administrator ETB', 'email' => $email]);
            $admin->forceFill(['role' => User::ROLE_ADMIN, 'password' => $password,
                'email_verified_at' => now(), 'remember_token' => null])->save();
            if (config('session.driver') === 'database' && Schema::hasTable('sessions')) {
                DB::table('sessions')->where('user_id', $admin->id)->delete();
            }
        });
        $this->info('Konto admin@etb.pl jest gotowe. Hasło zapisano bez wysyłania wiadomości.');

        return self::SUCCESS;
    }
}
