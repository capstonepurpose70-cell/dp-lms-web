<?php

namespace App\Console\Commands;

use App\Mail\OtpMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Sends one sample code email and shows the provider's answer. The app itself only logs mail
 * failures, so this is how to confirm the mail settings after a deploy.
 */
class MailCheck extends Command
{
    protected $signature = 'mail:check {email : Inbox that receives the sample}';

    protected $description = 'Send a sample email and report whether the mail provider accepted it';

    public function handle(): int
    {
        $email = trim((string) $this->argument('email'));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('That is not a valid email address.');
            return self::FAILURE;
        }

        $this->line('Mailer: ' . config('mail.default') . '   From: ' . config('mail.from.address'));

        try {
            Mail::to($email)->send(new OtpMail('123456', 'DP-LMS mail check'));
        } catch (\Throwable $e) {
            $this->error('Not sent: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info("Accepted for delivery to {$email}. Check the inbox and the spam folder.");
        return self::SUCCESS;
    }
}
