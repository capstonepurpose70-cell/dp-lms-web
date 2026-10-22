<?php

namespace App\Console\Commands;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Localhost LANG: kapag hindi gumagana ang email (o @dplms.test ang demo account),
 * dito kunin ang 6-digit login code. Pareho sa OtpService::send() pero
 * sa terminal ipinapakita, hindi ini-email. Hindi tatakbo sa production.
 */
class LocalDemoOtp extends Command
{
    protected $signature = 'demo:otp {login : Email, 12-digit LRN or mobile number}';

    protected $description = 'Local-only: show a new login code for an account (when email is not working)';

    public function handle(): int
    {
        if (! app()->environment('local')) {
            $this->error('demo:otp only runs when APP_ENV=local.');
            return self::FAILURE;
        }

        $user = User::resolveByIdentifier(trim((string) $this->argument('login')));
        if (! $user) {
            $this->error('No account found for that email / LRN / mobile number.');
            return self::FAILURE;
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::where('user_id', $user->id)->where('purpose', 'login')->delete();
        OtpCode::create([
            'user_id'    => $user->id,
            'code'       => Hash::make($code),
            'purpose'    => 'login',
            'expires_at' => now()->addMinutes(5),
            'used'       => false,
        ]);

        $this->info("Login code for {$user->name}: {$code}");
        $this->line('Valid for 5 minutes. Type it on the "Enter code" page (after the password step).');

        return self::SUCCESS;
    }
}
