<?php

namespace App\Services;

use App\Models\CustomerEmailSetting;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class CustomerEmailService
{
    public function canSendAuthEvent(string $event): bool
    {
        $settings = CustomerEmailSetting::query()->first();

        if (! $settings?->is_active) {
            return false;
        }

        return match ($event) {
            'account_creation' => (bool) $settings->send_account_creation_emails,
            'verification' => (bool) $settings->send_email_verification_emails,
            'password_reset' => (bool) $settings->send_password_reset_emails,
            default => false,
        };
    }

    public function canSendOrderEmails(): bool
    {
        $settings = CustomerEmailSetting::query()->first();

        return (bool) ($settings?->is_active && $settings->send_order_emails);
    }

    public function sendAuthMail(string $email, string $subject, string $body): void
    {
        $this->sendMail($email, $subject, $body, 'auth');
    }

    public function sendOrderMail(string $email, string $subject, string $body): void
    {
        $this->sendMail($email, $subject, $body, 'order');
    }

    private function sendMail(string $email, string $subject, string $body, string $channel): void
    {
        $settings = CustomerEmailSetting::query()->first();

        if (! $settings?->is_active) {
            throw new RuntimeException('Customer email delivery is not configured right now.');
        }

        $profile = $this->resolveProfile($settings, $channel);

        if (
            $profile['from_email'] === null ||
            $profile['smtp_host'] === null ||
            $profile['smtp_port'] === null ||
            $profile['smtp_username'] === null ||
            $profile['smtp_password'] === null
        ) {
            throw new RuntimeException(
                $channel === 'order'
                    ? 'Order email delivery is not configured right now.'
                    : 'Customer email delivery is not configured right now.'
            );
        }

        $smtpScheme = match (strtolower((string) $profile['smtp_encryption'])) {
            'ssl' => 'smtps',
            'tls' => 'tls',
            default => null,
        };

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $profile['smtp_host'],
            'mail.mailers.smtp.port' => $profile['smtp_port'],
            'mail.mailers.smtp.scheme' => $smtpScheme,
            'mail.mailers.smtp.encryption' => $profile['smtp_encryption'],
            'mail.mailers.smtp.username' => $profile['smtp_username'],
            'mail.mailers.smtp.password' => $profile['smtp_password'],
            'mail.from.address' => $profile['from_email'],
            'mail.from.name' => $profile['from_name'],
        ]);

        try {
            Mail::raw($body, function ($message) use ($email, $profile, $subject): void {
                $message->to($email)
                    ->from($profile['from_email'], $profile['from_name'])
                    ->replyTo($profile['reply_to_email'], $profile['from_name'])
                    ->subject($subject);
            });
        } catch (\Throwable $throwable) {
            throw new RuntimeException(
                $channel === 'order'
                    ? 'Unable to send order email right now. Please verify order email settings.'
                    : 'Unable to send customer email right now. Please verify email and OTP settings.'
            );
        }
    }

    private function resolveProfile(CustomerEmailSetting $settings, string $channel): array
    {
        $authFromName = $settings->from_name ?: 'Little Divinity';
        $authFromEmail = $settings->from_email ?: null;
        $authReplyToEmail = $settings->reply_to_email ?: $authFromEmail;
        $authUsername = $settings->smtp_username ?: $authFromEmail;
        $authPassword = $settings->smtp_password ?: null;

        if ($channel !== 'order') {
            return [
                'from_name' => $authFromName,
                'from_email' => $authFromEmail,
                'reply_to_email' => $authReplyToEmail,
                'smtp_host' => $settings->smtp_host ?: null,
                'smtp_port' => $settings->smtp_port ?: null,
                'smtp_encryption' => $settings->smtp_encryption ?: null,
                'smtp_username' => $authUsername,
                'smtp_password' => $authPassword,
            ];
        }

        $orderFromName = $settings->order_from_name ?: $authFromName;
        $orderFromEmail = $settings->order_from_email ?: $authFromEmail;
        $orderReplyToEmail = $settings->order_reply_to_email ?: $orderFromEmail ?: $authReplyToEmail;
        $orderUsername = $settings->order_smtp_username ?: $orderFromEmail ?: $authUsername;
        $orderPassword = $settings->order_smtp_password ?: $authPassword;

        return [
            'from_name' => $orderFromName,
            'from_email' => $orderFromEmail,
            'reply_to_email' => $orderReplyToEmail,
            'smtp_host' => $settings->smtp_host ?: null,
            'smtp_port' => $settings->smtp_port ?: null,
            'smtp_encryption' => $settings->smtp_encryption ?: null,
            'smtp_username' => $orderUsername,
            'smtp_password' => $orderPassword,
        ];
    }
}
