<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $privacyPolicy = <<<'HTML'
<p>Last updated: January 15, 2026</p>
<p>This Privacy Policy describes how Kanakshi.in (the "Site", "we", "us", or "our") collects, uses, and discloses your personal information when you visit, use our services, or make a purchase from kanakshi.in or otherwise communicate with us regarding the Site.</p>
<p>Please read this Privacy Policy carefully.</p>
<h2>Changes to This Privacy Policy</h2>
<p>We may update this Privacy Policy from time to time to reflect changes to our practices or for operational, legal, or regulatory reasons.</p>
<h2>How We Collect and Use Your Personal Information</h2>
<p>We collect personal information to provide the Services, process orders, manage accounts, arrange shipping, handle returns and exchanges, communicate with you, improve our Services, and protect the Site and its users.</p>
<h2>What Personal Information We Collect</h2>
<ul>
  <li><strong>Contact details</strong> including name, address, phone number, and email.</li>
  <li><strong>Order information</strong> including billing address, shipping address, payment confirmation, email address, and phone number.</li>
  <li><strong>Account information</strong> including username, password, and account security information.</li>
  <li><strong>Customer support information</strong> included in communications with us.</li>
</ul>
<h2>Cookies</h2>
<p>We use cookies and similar technologies to power and improve the Site, remember preferences, run analytics, and understand user interaction with the Services.</p>
<h2>How We Disclose Personal Information</h2>
<p>We may disclose information to vendors, payment processors, shipping and fulfillment partners, business and marketing partners, affiliates, or where legally required.</p>
<h2>Your Rights</h2>
<p>Depending on where you live, you may have rights to access, correct, delete, or port your personal information, restrict processing, withdraw consent, appeal, and manage communication preferences.</p>
<h2>Contact</h2>
<p>If you have questions about our privacy practices or this Privacy Policy, please email us at support@kanakshi.in.</p>
HTML;

        $termsConditions = <<<'HTML'
<p><strong>OVERVIEW</strong><br>This website is operated by Kanakshi.in. By visiting our site and/or purchasing something from us, you engage in our Service and agree to be bound by these Terms of Service, including all terms, conditions, policies and notices stated here.</p>
<p><strong>SECTION 1 - ONLINE STORE TERMS</strong><br>You must be at least the age of majority in your state or province of residence to use this site and may not use our products for any illegal or unauthorized purpose.</p>
<p><strong>SECTION 2 - GENERAL CONDITIONS</strong><br>We reserve the right to refuse Service to anyone for any reason at any time.</p>
<p><strong>SECTION 3 - ACCURACY, COMPLETENESS AND TIMELINESS OF INFORMATION</strong><br>We are not responsible if information made available on this site is not accurate, complete or current.</p>
<p><strong>SECTION 4 - MODIFICATIONS TO THE SERVICE AND PRICES</strong><br>Prices for products are subject to change without notice and the Service may be modified or discontinued at any time.</p>
<p><strong>SECTION 5 - PRODUCTS OR SERVICES</strong><br>Certain products may be available exclusively online, may have limited quantities, and are subject to return or exchange only according to our Refund Policy.</p>
<p><strong>SECTION 6 - ACCURACY OF BILLING AND ACCOUNT INFORMATION</strong><br>You agree to provide current, complete and accurate purchase and account information for all purchases made at our store.</p>
<p><strong>SECTION 7 - OPTIONAL TOOLS</strong><br>We may provide access to third-party tools “as is” and “as available” without warranties or conditions of any kind.</p>
<p><strong>SECTION 8 - THIRD-PARTY LINKS</strong><br>We are not responsible for examining or evaluating the content or accuracy of third-party websites.</p>
<p><strong>SECTION 9 - USER COMMENTS, FEEDBACK AND OTHER SUBMISSIONS</strong><br>Any comments or submissions you send us may be used by us without restriction.</p>
<p><strong>SECTION 10 - PERSONAL INFORMATION</strong><br>Your submission of personal information through the store is governed by our Privacy Policy.</p>
<p><strong>SECTION 11 - ERRORS, INACCURACIES AND OMISSIONS</strong><br>We reserve the right to correct any errors, inaccuracies, or omissions and to change or update information or cancel orders where information is inaccurate.</p>
<p><strong>SECTION 12 - PROHIBITED USES</strong><br>You may not use the site or its content for unlawful, abusive, fraudulent, harmful, malicious, or security-circumventing purposes.</p>
<p><strong>SECTION 13 - DISCLAIMER OF WARRANTIES; LIMITATION OF LIABILITY</strong><br>The Service and all products are provided “as is” and “as available,” without warranties of any kind.</p>
<p><strong>SECTION 18 - GOVERNING LAW</strong><br>These Terms of Service and any separate agreements are governed by the laws of India.</p>
<p><strong>SECTION 20 - CONTACT INFORMATION</strong><br>Questions about the Terms of Service should be sent to us at support@kanakshi.in.</p>
HTML;

        $returnPolicy = <<<'HTML'
<p>We have a 30-day return policy, which means you have 30 days after receiving your item to request a return.</p>
<p>To be eligible for a return, your item must be in the same condition that you received it, unworn or unused, with tags, and in its original packaging. You’ll also need the receipt or proof of purchase.</p>
<p>To start a return, you can contact us at <a href="mailto:support@kanakshi.in">support@kanakshi.in</a>.</p>
<p>If your return is accepted, we’ll send you a return shipping label along with instructions on how and where to send your package.</p>
<p><strong>Damages and issues</strong><br>Please inspect your order upon reception and contact us immediately if the item is defective, damaged, or incorrect.</p>
<p><strong>Exceptions / non-returnable items</strong><br>Certain types of items cannot be returned, including perishable goods, custom products, personal care goods, hazardous materials, flammable liquids, gases, sale items, and gift cards.</p>
<p><strong>Exchanges</strong><br>The fastest way to ensure you get what you want is to return the item you have, and once the return is accepted, make a separate purchase for the new item.</p>
<p><strong>Refunds</strong><br>If approved, you’ll be automatically refunded on your original payment method within 10 business days. If more than 15 business days have passed since approval, please contact us at support@kanakshi.in.</p>
HTML;

        DB::table('store_settings')->updateOrInsert(
            ['id' => 1],
            [
                'site_name' => 'Kanakshi.in',
                'business_name' => 'Kanakshi.in',
                'business_email' => 'no-reply@kanakshi.in',
                'business_phone' => null,
                'support_email' => 'support@kanakshi.in',
                'support_phone' => null,
                'whatsapp_number' => null,
                'address_line1' => null,
                'city' => null,
                'state' => null,
                'pincode' => null,
                'country' => 'India',
                'privacy_policy' => $privacyPolicy,
                'terms_conditions' => $termsConditions,
                'return_policy' => $returnPolicy,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('menu_items')->updateOrInsert(
            ['location' => 'footer', 'title' => 'Refund Policy'],
            [
                'url' => '/pages/refund-policy',
                'target' => '_self',
                'config' => json_encode([], JSON_UNESCAPED_SLASHES),
                'sort_order' => 5,
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('menu_items')
            ->where('location', 'footer')
            ->where('title', 'Track Your Order')
            ->update([
                'sort_order' => 6,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('menu_items')
            ->where('location', 'footer')
            ->where('title', 'Refund Policy')
            ->delete();
    }
};
