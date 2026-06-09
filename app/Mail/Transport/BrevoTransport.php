<?php

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

/**
 * Sends mail through Brevo's HTTP API (https://api.brevo.com/v3/smtp/email).
 *
 * Why: hosts like Railway block outbound SMTP ports (25/465/587), so the
 * normal "smtp" mailer times out. Brevo's REST API uses HTTPS (port 443),
 * which is always allowed, so OTP / invite / approval emails actually deliver.
 *
 * Activated by setting MAIL_MAILER=brevo and BREVO_API_KEY in the environment.
 */
class BrevoTransport extends AbstractTransport
{
    public function __construct(private string $apiKey)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $to = [];
        foreach ($email->getTo() as $addr) {
            $to[] = array_filter([
                'email' => $addr->getAddress(),
                'name'  => $addr->getName() ?: null,
            ]);
        }

        $from   = $email->getFrom()[0] ?? null;
        $sender = array_filter([
            'email' => $from?->getAddress(),
            'name'  => $from?->getName() ?: null,
        ]);

        $payload = [
            'sender'  => $sender,
            'to'      => $to,
            'subject' => $email->getSubject() ?? '',
        ];

        $html = $email->getHtmlBody();
        $text = $email->getTextBody();
        if (!empty($html)) {
            $payload['htmlContent'] = is_string($html) ? $html : (string) $html;
        }
        if (!empty($text)) {
            $payload['textContent'] = is_string($text) ? $text : (string) $text;
        }
        // Brevo requires at least one body
        if (empty($payload['htmlContent']) && empty($payload['textContent'])) {
            $payload['textContent'] = ' ';
        }

        $response = Http::withHeaders([
            'api-key'      => $this->apiKey,
            'accept'       => 'application/json',
            'content-type' => 'application/json',
        ])->timeout(15)->post('https://api.brevo.com/v3/smtp/email', $payload);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'Brevo email send failed: ' . $response->status() . ' ' . $response->body()
            );
        }
    }

    public function __toString(): string
    {
        return 'brevo';
    }
}