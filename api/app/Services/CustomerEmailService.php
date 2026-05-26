<?php

namespace App\Services;

use App\Models\CustomerEmailSetting;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class CustomerEmailService
{
    public const AUTH_FROM_NAME = 'Little Divinity';

    public const AUTH_FROM_EMAIL = 'noreply@littledivinity.com';

    public const ORDER_FROM_NAME = 'Little Divinity Orders';

    public const ORDER_FROM_EMAIL = 'order@littledivinity.com';

    public const SMTP_HOST = 'smtp.hostinger.com';

    public const SMTP_PORT = 465;

    public const SMTP_ENCRYPTION = 'ssl';

    public const AUTH_SMTP_USERNAME = 'noreply@littledivinity.com';

    public const AUTH_SMTP_PASSWORD = 'Littledivinity@123';

    public const ORDER_SMTP_USERNAME = 'order@littledivinity.com';

    public const ORDER_SMTP_PASSWORD = 'Littledivinity@123';

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

        $profile = $this->resolveProfile($channel);

        if (! $this->profileHasTransport($profile)) {
            throw new RuntimeException(
                $channel === 'order'
                    ? 'Order email delivery is not configured right now.'
                    : 'Customer email delivery is not configured right now.'
            );
        }

        try {
            $this->sendViaProfile($email, $subject, $body, $profile);
            return;
        } catch (\Throwable $throwable) {
            throw new RuntimeException(
                $channel === 'order'
                    ? 'Unable to send order email right now. Please verify order email settings.'
                    : 'Unable to send customer email right now. Please verify email and OTP settings.'
            );
        }
    }

    private function resolveProfile(string $channel): array
    {
        if ($channel !== 'order') {
            return [
                'from_name' => self::AUTH_FROM_NAME,
                'from_email' => self::AUTH_FROM_EMAIL,
                'reply_to_email' => self::AUTH_FROM_EMAIL,
                'smtp_host' => self::SMTP_HOST,
                'smtp_port' => self::SMTP_PORT,
                'smtp_encryption' => self::SMTP_ENCRYPTION,
                'smtp_username' => self::AUTH_SMTP_USERNAME,
                'smtp_password' => self::AUTH_SMTP_PASSWORD,
            ];
        }

        return [
            'from_name' => self::ORDER_FROM_NAME,
            'from_email' => self::ORDER_FROM_EMAIL,
            'reply_to_email' => self::ORDER_FROM_EMAIL,
            'smtp_host' => self::SMTP_HOST,
            'smtp_port' => self::SMTP_PORT,
            'smtp_encryption' => self::SMTP_ENCRYPTION,
            'smtp_username' => self::ORDER_SMTP_USERNAME,
            'smtp_password' => self::ORDER_SMTP_PASSWORD,
        ];
    }

    private function sendViaProfile(string $email, string $subject, string $body, array $profile): void
    {
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

        Mail::raw($body, function ($message) use ($email, $profile, $subject): void {
            $message->to($email)
                ->from($profile['from_email'], $profile['from_name'])
                ->replyTo($profile['reply_to_email'], $profile['from_name'])
                ->subject($subject);
        });
    }

    private function profileHasTransport(array $profile): bool
    {
        return $profile['from_email'] !== null
            && $profile['smtp_host'] !== null
            && $profile['smtp_port'] !== null
            && $profile['smtp_username'] !== null
            && $profile['smtp_password'] !== null;
    }
}
