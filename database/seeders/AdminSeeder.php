<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // PUBLIC ang repo — HUWAG maglagay ng password dito.
        // Itakda ang ADMIN_SEED_PASSWORD sa .env (o Railway variables). Kung wala,
        // gagawa ng random na password at ipapakita LANG sa console nang isang beses.
        $email    = env('ADMIN_SEED_EMAIL', 'admin@sdnhs.edu.ph');
        $password = env('ADMIN_SEED_PASSWORD');

        if (Admin::where('email', $email)->exists()) {
            $this->command?->info("Admin {$email} already exists — skipped (password not changed).");
            return;
        }

        if (empty($password)) {
            $password = Str::password(20);
            $this->command?->warn("ADMIN_SEED_PASSWORD not set. Generated password for {$email}: {$password}");
            $this->command?->warn('Save it now and change it after first login — it will not be shown again.');
        }

        Admin::create([
            'name'           => 'School Administrator',
            'email'          => $email,
            'password'       => Hash::make($password),
            'is_super_admin' => true,
        ]);
    }
}
