<?php

namespace App\Services;

use App\Models\CustomerEmailSetting;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class CustomerEmailService
{
    public const AUTH_FROM_NAME = 'Kanakshi.in';

    public const AUTH_FROM_EMAIL = 'no-reply@kanakshi.in';

    public const ORDER_FROM_NAME = 'Kanakshi.in Orders';

    public const ORDER_FROM_EMAIL = 'no-reply@kanakshi.in';

    public const SMTP_HOST = 'smtp.hostinger.com';

    public const SMTP_PORT = 465;

    public const SMTP_ENCRYPTION = 'ssl';

    public const AUTH_SMTP_USERNAME = 'no-reply@kanakshi.in';

    public const ORDER_SMTP_USERNAME = 'no-reply@kanakshi.in';

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

    public function isCustomerEmailDeliveryActive(): bool
    {
        $settings = CustomerEmailSetting::query()->first();

        return (bool) ($settings?->is_active && $this->profileHasTransport($this->resolveProfile('auth')));
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
        $settings = CustomerEmailSetting::query()->first();

        if ($channel !== 'order') {
            return [
                'from_name' => $settings?->from_name ?: self::AUTH_FROM_NAME,
                'from_email' => $settings?->from_email ?: self::AUTH_FROM_EMAIL,
                'reply_to_email' => $settings?->reply_to_email ?: env('CUSTOMER_AUTH_REPLY_TO_EMAIL', env('STORE_SUPPORT_EMAIL', 'support@kanakshi.in')),
                'smtp_host' => $settings?->smtp_host ?: self::SMTP_HOST,
                'smtp_port' => $settings?->smtp_port ?: self::SMTP_PORT,
                'smtp_encryption' => $settings?->smtp_encryption ?: self::SMTP_ENCRYPTION,
                'smtp_username' => $settings?->smtp_username ?: self::AUTH_SMTP_USERNAME,
                'smtp_password' => $settings?->smtp_password
                    ?: env('CUSTOMER_AUTH_SMTP_PASSWORD')
                    ?: env('CUSTOMER_SMTP_PASSWORD')
                    ?: env('SMTP_SETTINGS_PASSWORD'),
            ];
        }

        return [
            'from_name' => $settings?->order_from_name ?: self::ORDER_FROM_NAME,
            'from_email' => $settings?->order_from_email ?: self::ORDER_FROM_EMAIL,
            'reply_to_email' => $settings?->order_reply_to_email ?: env('CUSTOMER_ORDER_REPLY_TO_EMAIL', env('STORE_SUPPORT_EMAIL', 'support@kanakshi.in')),
            'smtp_host' => $settings?->smtp_host ?: self::SMTP_HOST,
            'smtp_port' => $settings?->smtp_port ?: self::SMTP_PORT,
            'smtp_encryption' => $settings?->smtp_encryption ?: self::SMTP_ENCRYPTION,
            'smtp_username' => $settings?->order_smtp_username ?: self::ORDER_SMTP_USERNAME,
            'smtp_password' => $settings?->order_smtp_password
                ?: env('CUSTOMER_ORDER_SMTP_PASSWORD')
                ?: env('CUSTOMER_SMTP_PASSWORD')
                ?: env('CUSTOMER_AUTH_SMTP_PASSWORD')
                ?: env('SMTP_SETTINGS_PASSWORD'),
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

        $channel = $profile['from_email'] === self::ORDER_FROM_EMAIL ? 'order' : 'auth';
        $html = view('emails.customer-brand', $this->buildEmailViewData($subject, $body, $channel))->render();

        Mail::html($html, function ($message) use ($email, $profile, $subject): void {
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

    private function buildEmailViewData(string $subject, string $body, string $channel): array
    {
        $store = StoreSetting::query()->first();
        $siteName = $store?->site_name ?: 'Kanakshi.in';
        $supportEmail = $store?->support_email ?: $store?->business_email ?: self::AUTH_FROM_EMAIL;
        $supportPhone = $store?->support_phone ?: $store?->business_phone;
        $siteUrl = $this->resolveSiteUrl($store?->custom_domain);
        $logoUrl = $this->resolveLogoUrl($store?->logo_url, $siteUrl);

        $parsed = $this->parseBody($subject, $body);

        return [
            'siteName' => $siteName,
            'siteUrl' => $siteUrl,
            'logoUrl' => $logoUrl,
            'supportEmail' => $supportEmail,
            'supportPhone' => $supportPhone,
            'subject' => $subject,
            'preheader' => $parsed['preheader'],
            'eyebrow' => $channel === 'order' ? 'Order Update' : 'Account & Security',
            'accentColor' => '#c5a059',
            'accentColorSoft' => '#f6efe1',
            'surfaceColor' => '#ffffff',
            'backgroundColor' => '#f5f1ea',
            'textColor' => '#1f1a17',
            'mutedColor' => '#6f655d',
            'greeting' => $parsed['greeting'],
            'paragraphs' => $parsed['paragraphs'],
            'otpCode' => $parsed['otpCode'],
            'details' => $parsed['details'],
            'detailTitle' => $parsed['detailTitle'],
            'listSections' => $parsed['listSections'],
            'actionUrl' => $parsed['actionUrl'],
            'actionLabel' => $parsed['actionLabel'],
            'closingLines' => $parsed['closingLines'],
        ];
    }

    private function parseBody(string $subject, string $body): array
    {
        $normalizedBody = trim(str_replace(["\r\n", "\r"], "\n", $body));
        $lines = array_values(array_filter(array_map('trim', explode("\n", $normalizedBody)), static fn ($line) => $line !== ''));

        $greeting = null;
        if (! empty($lines) && preg_match('/^(hello|dear)\b/i', $lines[0])) {
            $greeting = array_shift($lines);
        }

        $closingLines = [];
        while (! empty($lines)) {
            $lastLine = end($lines);
            if ($lastLine === false) {
                break;
            }

            if (preg_match('/^(warm regards|regards|thanks|thank you|team kanakshi\.in|kanakshi\.in)$/i', $lastLine)) {
                array_unshift($closingLines, array_pop($lines));
                continue;
            }

            break;
        }

        $otpCode = null;
        if (preg_match('/\b(\d{4,8})\b/', $subject . "\n" . $normalizedBody, $otpMatch) && preg_match('/otp|one[- ]time/i', $subject . "\n" . $normalizedBody)) {
            $otpCode = $otpMatch[1];
        }

        preg_match('/https?:\/\/[^\s]+/i', $normalizedBody, $urlMatch);
        $actionUrl = $urlMatch[0] ?? null;

        $detailTitle = null;
        $details = [];
        $paragraphs = [];
        $listSections = [];
        $activeListTitle = null;

        foreach ($lines as $line) {
            if (preg_match('/^-{3,}$/', $line)) {
                continue;
            }

            if (preg_match('/^(items|claim details|guarantee details|appraisal details|reason for rejection|details):$/i', $line, $headingMatch)) {
                $heading = strtolower(trim($headingMatch[1]));

                if ($heading === 'items' || $heading === 'reason for rejection') {
                    $activeListTitle = $headingMatch[1];
                    continue;
                }

                $activeListTitle = null;
                $detailTitle = ucwords($headingMatch[1]);
                continue;
            }

            if (preg_match('/^([^:]{2,80}):\s*(.+)$/', $line, $detailMatch) && ! str_contains($line, '://')) {
                $details[] = [
                    'label' => trim($detailMatch[1]),
                    'value' => trim($detailMatch[2]),
                ];
                continue;
            }

            if ($activeListTitle !== null && strtolower($activeListTitle) === 'items' && ! preg_match('/\s+x\s+\d+$/i', $line)) {
                $activeListTitle = null;
            }

            if ($activeListTitle !== null) {
                $sectionKey = strtolower($activeListTitle);
                if (! isset($listSections[$sectionKey])) {
                    $listSections[$sectionKey] = [
                        'title' => ucwords($activeListTitle),
                        'items' => [],
                    ];
                }

                $listSections[$sectionKey]['items'][] = $line;
                continue;
            }

            $paragraphs[] = $line;
        }

        if ($detailTitle === null && ! empty($details)) {
            $detailTitle = 'Summary';
        }

        $actionLabel = 'Open Kanakshi.in';
        if ($actionUrl) {
            if (str_contains(strtolower($subject . ' ' . $normalizedBody), 'track')) {
                $actionLabel = 'Track Status';
            } elseif (str_contains(strtolower($subject . ' ' . $normalizedBody), 'verify')) {
                $actionLabel = 'View Verification';
            } elseif (str_contains(strtolower($subject . ' ' . $normalizedBody), 'warranty')) {
                $actionLabel = 'Check Warranty Status';
            } elseif (str_contains(strtolower($subject . ' ' . $normalizedBody), 'buyback')) {
                $actionLabel = 'Review Request';
            }
        }

        return [
            'greeting' => $greeting,
            'preheader' => $paragraphs[0] ?? $subject,
            'paragraphs' => $paragraphs,
            'otpCode' => $otpCode,
            'details' => $details,
            'detailTitle' => $detailTitle,
            'listSections' => array_values($listSections),
            'actionUrl' => $actionUrl,
            'actionLabel' => $actionLabel,
            'closingLines' => ! empty($closingLines) ? $closingLines : ['Team Kanakshi.in'],
        ];
    }

    private function resolveSiteUrl(?string $customDomain): string
    {
        $domain = trim((string) $customDomain);

        if ($domain === '') {
            return 'https://kanakshi.in';
        }

        if (preg_match('/^https?:\/\//i', $domain)) {
            return rtrim($domain, '/');
        }

        return 'https://' . trim($domain, '/');
    }

    private function resolveLogoUrl(?string $logoUrl, string $siteUrl): string
    {
        $logoUrl = trim((string) $logoUrl);

        if ($logoUrl === '') {
            return $siteUrl . '/logo.jpg';
        }

        if (preg_match('/^https?:\/\//i', $logoUrl)) {
            return $logoUrl;
        }

        return $siteUrl . '/' . ltrim($logoUrl, '/');
    }
}
