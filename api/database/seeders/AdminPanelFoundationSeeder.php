<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminPanelFoundationSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('store_settings')->updateOrInsert(
            ['id' => 1],
            [
                'site_name' => 'Little Divinity',
                'site_tagline' => 'Handcrafted brass decor, pooja pieces, and meaningful gifting.',
                'business_name' => 'Little Divinity',
                'business_email' => env('STORE_BUSINESS_EMAIL', 'noreply@saaszo.in'),
                'business_phone' => '+91 9910212007',
                'support_email' => env('STORE_SUPPORT_EMAIL', 'noreply@saaszo.in'),
                'support_phone' => '+91 9910212007',
                'whatsapp_number' => '+91 9910212007',
                'custom_domain' => 'littledivinity.com',
                'logo_url' => '/logo.jpg',
                'favicon_url' => '/favicon.ico',
                'currency' => 'INR',
                'currency_symbol' => '₹',
                'timezone' => 'Asia/Kolkata',
                'language' => 'en',
                'address_line1' => 'E-3, Ground Floor Sector -3',
                'city' => 'Noida',
                'state' => 'Uttar Pradesh',
                'pincode' => '201301',
                'country' => 'India',
                'invoice_prefix' => 'LD',
                'show_logo_on_invoice' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('email_settings')->updateOrInsert(
            ['id' => 1],
            [
                'mailer' => 'smtp',
                'from_name' => 'ecomeservice for littledivinity',
                'from_email' => env('STORE_SUPPORT_EMAIL', 'noreply@saaszo.in'),
                'reply_to_email' => env('STORE_SUPPORT_EMAIL', 'noreply@saaszo.in'),
                'smtp_host' => 'smtp.hostinger.com',
                'smtp_port' => 465,
                'smtp_encryption' => 'ssl',
                'smtp_username' => env('STORE_SUPPORT_EMAIL', 'noreply@saaszo.in'),
                'smtp_password' => env('SMTP_SETTINGS_PASSWORD'),
                'imap_host' => 'imap.hostinger.com',
                'imap_port' => 993,
                'imap_encryption' => 'ssl',
                'pop_host' => 'pop.hostinger.com',
                'pop_port' => 995,
                'pop_encryption' => 'ssl',
                'send_otp_emails' => true,
                'send_password_reset_emails' => true,
                'send_account_creation_emails' => true,
                'send_order_emails' => true,
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $paymentGateways = [
            ['provider' => 'razorpay', 'display_name' => 'Razorpay', 'sort_order' => 1],
            ['provider' => 'phonepe', 'display_name' => 'PhonePe', 'sort_order' => 2],
            ['provider' => 'paytm', 'display_name' => 'Paytm', 'sort_order' => 3],
            ['provider' => 'cod', 'display_name' => 'Cash on Delivery', 'sort_order' => 4, 'is_active' => true, 'is_test_mode' => false],
        ];

        foreach ($paymentGateways as $gateway) {
            DB::table('payment_gateway_settings')->updateOrInsert(
                ['provider' => $gateway['provider']],
                array_merge([
                    'is_active' => false,
                    'is_test_mode' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ], $gateway)
            );
        }

        DB::table('delivery_partner_settings')->updateOrInsert(
            ['code' => 'manual'],
            [
                'name' => 'Manual Shipping',
                'is_active' => true,
                'is_default' => true,
                'tracking_url_template' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $socialLinks = [
            ['platform' => 'facebook', 'title' => 'Facebook', 'url' => 'https://www.facebook.com/uniquebrasscollection', 'sort_order' => 1],
            ['platform' => 'instagram', 'title' => 'Instagram', 'url' => 'https://www.instagram.com/the_advitya/', 'sort_order' => 2],
            ['platform' => 'youtube', 'title' => 'YouTube', 'url' => 'https://www.youtube.com/@the_advitya', 'sort_order' => 3],
            ['platform' => 'linkedin', 'title' => 'LinkedIn', 'url' => 'https://www.linkedin.com/company/theadvitya/', 'sort_order' => 4],
        ];

        DB::table('social_links')
            ->whereNotIn('platform', array_column($socialLinks, 'platform'))
            ->delete();

        foreach ($socialLinks as $link) {
            DB::table('social_links')->updateOrInsert(
                ['platform' => $link['platform']],
                array_merge($link, [
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }

        $menuItems = [
            ['location' => 'header', 'title' => 'Hindu Deities', 'url' => '/shop?category=hindu-dieties', 'sort_order' => 1, 'config' => ['submenu' => ['Ganesha Idols', 'Krishna Idols', 'Ram Darbar']]],
            ['location' => 'header', 'title' => 'Home Kitchen', 'url' => '/shop?category=home-kitchen', 'sort_order' => 2, 'config' => ['submenu' => ['Spice Boxes', 'Serving Trays', 'Utility Decor']]],
            ['location' => 'header', 'title' => 'Home Decor', 'url' => '/shop?category=home-decor', 'sort_order' => 3, 'config' => ['submenu' => ['Wall Decor', 'Table Decor', 'Candle Stands']]],
            ['location' => 'header', 'title' => 'Pooja Decor', 'url' => '/shop?category=pooja-decor', 'sort_order' => 4, 'config' => ['submenu' => ['Brass Singhasan', 'Incense Stand', 'Brass Spoon', 'Diya', 'Brass Chowki', 'Bells', 'Pooja Thali', 'Wooden Mandir Decor']]],
            ['location' => 'header', 'title' => "Mother's Day collection", 'url' => '/shop?category=mothers-day-collection', 'sort_order' => 5, 'config' => ['submenu' => ['Gifting Picks', 'Wall Decor', 'Home Styling']]],
            ['location' => 'header', 'title' => 'More', 'url' => '/shop', 'sort_order' => 6, 'config' => ['submenu' => ['New Arrivals', 'Festival Categories', 'All Collections']]],
            ['location' => 'footer', 'title' => 'About Us', 'url' => '/pages/about-us', 'sort_order' => 1],
            ['location' => 'footer', 'title' => 'Contact', 'url' => '/pages/contact', 'sort_order' => 2],
            ['location' => 'footer', 'title' => 'Privacy Policy', 'url' => '/pages/privacy-policy', 'sort_order' => 3],
            ['location' => 'footer', 'title' => 'Terms & Conditions', 'url' => '/pages/terms-conditions', 'sort_order' => 4],
            ['location' => 'footer', 'title' => 'Track Your Order', 'url' => '/track-order', 'sort_order' => 5],
        ];

        $headerTitles = array_column(array_filter($menuItems, static fn (array $item): bool => $item['location'] === 'header'), 'title');
        $footerTitles = array_column(array_filter($menuItems, static fn (array $item): bool => $item['location'] === 'footer'), 'title');

        DB::table('menu_items')
            ->where('location', 'header')
            ->whereNotIn('title', $headerTitles)
            ->delete();
        DB::table('menu_items')
            ->where('location', 'footer')
            ->whereNotIn('title', $footerTitles)
            ->delete();

        foreach ($menuItems as $menuItem) {
            DB::table('menu_items')->updateOrInsert(
                ['location' => $menuItem['location'], 'title' => $menuItem['title']],
                array_merge($menuItem, [
                    'target' => '_self',
                    'config' => json_encode($menuItem['config'] ?? [], JSON_UNESCAPED_SLASHES),
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }

        $sections = [
            [
                'section_key' => 'hero',
                'section_type' => 'hero',
                'label' => 'Homepage Hero',
                'title' => 'Mother\'s Day Collection',
                'subtitle' => 'Little Divinity',
                'heading' => 'Hero slider, slider text, and side banners are managed from admin.',
                'config' => [
                    'slides' => [
                        ['title' => "Mother's Day Collection", 'image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_banner_4ab_copy_1_800x.jpg_v/screen.png', 'alt' => "Mother's Day gifting collection"],
                        ['title' => 'Brass English Watch', 'image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_2/screen.png', 'alt' => 'Brass English watch collection'],
                        ['title' => 'Ritual Essentials', 'image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_whatsapp_image_2026_02_20_at_2/screen.png', 'alt' => 'Sacred incense decor'],
                        ['title' => 'Buddha Collection', 'image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_your_paragraph_text_2025_10_2/screen.png', 'alt' => 'Buddha collection'],
                        ['title' => 'Wooden Collection', 'image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_whatsapp_image_2026_02_20_at_3/screen.png', 'alt' => 'Wooden collection'],
                    ],
                    'promos' => [
                        ['title' => 'Wall Decor Collection', 'subtitle' => 'Designed for thoughtful spaces', 'image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_17_940343c1_0d70_4490_907d/screen.png', 'href' => '/shop?category=wall-decor'],
                        ['title' => 'Stonework Collection', 'subtitle' => 'Timeless pieces for every space', 'image' => '/reference-assets/image_from_https_theadvitya.com_cdn_shop_files_untitled_design_2025_10_1/screen.png', 'href' => '/shop?category=home-decor'],
                    ],
                ],
                'sort_order' => 1,
            ],
            [
                'section_key' => 'best-sellers',
                'section_type' => 'featured_products',
                'label' => 'Best Sellers',
                'title' => 'Most Loved Across The Storefront',
                'subtitle' => 'Best Sellers',
                'heading' => 'Featured products section managed from admin dashboard.',
                'button_text' => 'Shop all',
                'button_url' => '/shop',
                'sort_order' => 2,
            ],
            [
                'section_key' => 'new-arrivals',
                'section_type' => 'promo_grid',
                'label' => 'New Arrivals',
                'title' => 'Fresh Pieces Worth A First Look',
                'subtitle' => 'New Arrivals',
                'heading' => 'Promotional two-image layout managed from admin.',
                'image_url' => '/storage/products/little-divinity-krishna.jpg',
                'side_image_url' => '/storage/products/little-divinity-candle-stand.png',
                'config' => [
                    'left_title' => 'Serving Boxes & Trays',
                    'left_href' => '/shop?category=home-kitchen',
                    'right_title' => 'Wooden Collection',
                    'right_href' => '/shop?category=wooden-collection',
                ],
                'sort_order' => 3,
            ],
        ];

        DB::table('homepage_sections')
            ->whereNotIn('section_key', array_column($sections, 'section_key'))
            ->delete();

        foreach ($sections as $section) {
            DB::table('homepage_sections')->updateOrInsert(
                ['section_key' => $section['section_key']],
                array_merge($section, [
                    'config' => json_encode($section['config'] ?? [], JSON_UNESCAPED_SLASHES),
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }

        $adminEmail = env('ADMIN_DEFAULT_EMAIL', 'admin@saaszo.in');
        $adminPassword = env('ADMIN_DEFAULT_PASSWORD');
        $supportEmail = env('STORE_SUPPORT_EMAIL', 'noreply@saaszo.in');

        if ($supportEmail !== $adminEmail) {
            User::query()
                ->where('email', $supportEmail)
                ->whereIn('role', ['super_admin', 'admin'])
                ->update([
                    'status' => 'inactive',
                    'is_active' => false,
                    'updated_at' => now(),
                ]);
        }

        if ($adminEmail && $adminPassword) {
            User::query()->updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => 'Little Divinity Admin',
                    'phone' => '+91 9910212007',
                    'role' => 'super_admin',
                    'status' => 'active',
                    'is_active' => true,
                    'is_protected' => true,
                    'two_factor_enabled' => true,
                    'two_factor_channel' => 'email',
                    'permissions' => ['all' => true],
                    'email_verified_at' => now(),
                    'password' => Hash::make($adminPassword),
                ]
            );
        }
    }
}
