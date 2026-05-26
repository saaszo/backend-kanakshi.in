<?php

namespace App\Services;

use App\Models\CustomerEmailSetting;
use App\Models\EmailSetting;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class CustomerEmailService
{
    public const AUTH_FROM_NAME = 'Little Divinity';

    public const AUTH_FROM_EMAIL = 'noreply@littledivinity.com';

    public const ORDER_FROM_NAME = 'Little Divinity Orders';

    public const ORDER_FROM_EMAIL = 'order@littledivinity.com';

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
        $legacySettings = EmailSetting::query()->first();
        $defaultHost = config('mail.mailers.smtp.host');
        $defaultPort = config('mail.mailers.smtp.port');
        $defaultEncryption = config('mail.mailers.smtp.encryption');
        $defaultUsername = config('mail.mailers.smtp.username');
        $defaultPassword = config('mail.mailers.smtp.password');

        $legacyHost = $legacySettings?->smtp_host ?: $defaultHost;
        $legacyPort = $legacySettings?->smtp_port ?: $defaultPort;
        $legacyEncryption = $legacySettings?->smtp_encryption ?: $defaultEncryption;
        $legacyUsername = $legacySettings?->smtp_username ?: $defaultUsername;
        $legacyPassword = $legacySettings?->smtp_password ?: $defaultPassword;

        $hasDedicatedAuthTransport = filled($settings->smtp_host)
            && filled($settings->smtp_port)
            && filled($settings->smtp_username)
            && filled($settings->smtp_password);

        $authFromName = self::AUTH_FROM_NAME;
        $authFromEmail = self::AUTH_FROM_EMAIL;
        $authReplyToEmail = self::AUTH_FROM_EMAIL;
        $authUsername = $hasDedicatedAuthTransport
            ? ($settings->smtp_username ?: $authFromEmail ?: $legacyUsername)
            : $legacyUsername;
        $authPassword = $hasDedicatedAuthTransport ? ($settings->smtp_password ?: $legacyPassword) : $legacyPassword;
        $authHost = $hasDedicatedAuthTransport ? ($settings->smtp_host ?: $legacyHost) : $legacyHost;
        $authPort = $hasDedicatedAuthTransport ? ($settings->smtp_port ?: $legacyPort) : $legacyPort;
        $authEncryption = $hasDedicatedAuthTransport ? ($settings->smtp_encryption ?: $legacyEncryption) : $legacyEncryption;

        if ($channel !== 'order') {
            return [
                'from_name' => $authFromName,
                'from_email' => $authFromEmail,
                'reply_to_email' => $authReplyToEmail,
                'smtp_host' => $authHost,
                'smtp_port' => $authPort,
                'smtp_encryption' => $authEncryption,
                'smtp_username' => $authUsername,
                'smtp_password' => $authPassword,
            ];
        }

        $hasDedicatedOrderTransport = filled($settings->smtp_host)
            && filled($settings->smtp_port)
            && filled($settings->order_smtp_username)
            && filled($settings->order_smtp_password);

        $orderFromName = self::ORDER_FROM_NAME;
        $orderFromEmail = self::ORDER_FROM_EMAIL;
        $orderReplyToEmail = self::ORDER_FROM_EMAIL;
        $orderUsername = $hasDedicatedOrderTransport
            ? ($settings->order_smtp_username ?: $orderFromEmail ?: $authUsername)
            : $authUsername;
        $orderPassword = $hasDedicatedOrderTransport ? ($settings->order_smtp_password ?: $authPassword) : $authPassword;

        return [
            'from_name' => $orderFromName,
            'from_email' => $orderFromEmail,
            'reply_to_email' => $orderReplyToEmail,
            'smtp_host' => $hasDedicatedOrderTransport ? ($settings->smtp_host ?: $legacyHost) : $authHost,
            'smtp_port' => $hasDedicatedOrderTransport ? ($settings->smtp_port ?: $legacyPort) : $authPort,
            'smtp_encryption' => $hasDedicatedOrderTransport ? ($settings->smtp_encryption ?: $legacyEncryption) : $authEncryption,
            'smtp_username' => $orderUsername,
            'smtp_password' => $orderPassword,
        ];
    }
}
