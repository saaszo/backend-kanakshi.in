<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductRegistration;
use App\Models\WarrantyClaim;
use App\Models\BuybackRequest;
use App\Models\RegistrationActivityLog;
use App\Models\Setting;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\CustomerEmailSetting;

class RegistryApiController extends Controller
{
    private function getSetting(string $key, mixed $default = null): mixed
    {
        $setting = Setting::query()->where('key_name', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Get searchable products list for autocomplete
     */
    public function getProducts(Request $request): JsonResponse
    {
        $search = $request->query('q', '');
        $products = Product::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'products' => $products
        ]);
    }

    /**
     * Register a product for Warranty & Buyback
     */
    public function register(Request $request): JsonResponse
    {
        $allowedSources = json_decode($this->getSetting('registry_allowed_sources', '["website","offline_store","amazon","other_marketplace"]'), true) ?: [];
        $allowedFileTypes = explode(',', $this->getSetting('registry_allowed_file_types', 'pdf,jpg,jpeg,png,webp'));
        $maxSizeMb = (int) $this->getSetting('registry_allowed_upload_size_mb', 5);

        $validator = Validator::make($request->all(), [
            'customer_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'whatsapp_number' => ['nullable', 'string', 'max:20'],
            'purchase_source' => ['required', 'string', 'in:' . implode(',', $allowedSources)],
            'order_or_bill_number' => ['required', 'string', 'max:100'],
            'purchase_date' => ['required', 'date', 'before_or_equal:today'],
            'product_id' => ['nullable', 'integer'],
            'product_name_snapshot' => ['required', 'string', 'max:255'],
            'serial_card_id' => ['nullable', 'string', 'max:100'],
            'source_store_name' => ['nullable', 'required_if:purchase_source,offline_store,other_marketplace', 'string', 'max:255'],
            'source_city' => ['nullable', 'required_if:purchase_source,offline_store', 'string', 'max:150'],
            'invoice_file' => [
                $request->input('purchase_source') === 'website' ? 'nullable' : 'required',
                'file',
                'max:' . ($maxSizeMb * 1024),
                function ($attribute, $value, $fail) use ($allowedFileTypes) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, $allowedFileTypes)) {
                        $fail("The invoice file type must be one of: " . implode(', ', $allowedFileTypes));
                    }
                }
            ],
            'product_image' => [
                'nullable',
                'file',
                'max:' . ($maxSizeMb * 1024),
                function ($attribute, $value, $fail) use ($allowedFileTypes) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, $allowedFileTypes) || $ext === 'pdf') {
                        $fail("The product image must be a valid photo (jpg, jpeg, png, webp).");
                    }
                }
            ],
            'notes' => ['nullable', 'string'],
            'whatsapp_opt_in' => ['nullable', 'boolean'],
            'terms_accepted' => ['required', 'accepted']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Prevent duplicate suspicious registrations
        $exists = ProductRegistration::query()
            ->where('purchase_source', $request->input('purchase_source'))
            ->where('order_or_bill_number', $request->input('order_or_bill_number'))
            ->where('product_name_snapshot', $request->input('product_name_snapshot'))
            ->where('email', $request->input('email'))
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This purchase has already been registered with these details. If you believe this is an error, please contact customer support.'
            ], 409);
        }

        // Handle File Uploads
        $invoicePath = null;
        if ($request->hasFile('invoice_file')) {
            $invoicePath = $request->file('invoice_file')->store('registry/invoices', 'public');
            $invoicePath = Storage::disk('public')->url($invoicePath);
        }

        $productImagePath = null;
        if ($request->hasFile('product_image')) {
            $productImagePath = $request->file('product_image')->store('registry/products', 'public');
            $productImagePath = Storage::disk('public')->url($productImagePath);
        }

        // Default verification configurations
        $status = 'pending_verification';
        $verifiedAt = null;
        $adminNotes = null;
        $buybackEligible = $this->getSetting('registry_allow_buyback', '1') === '1';

        // Auto Verification for internal website orders
        if ($request->input('purchase_source') === 'website' && $this->getSetting('registry_auto_verify_website_orders', '1') === '1') {
            $orderNumber = trim($request->input('order_or_bill_number'));
            $order = Order::query()->where('order_number', $orderNumber)->first();

            if ($order) {
                // Match email or phone for verification signal
                $emailMatch = strtolower($order->user?->email ?? '') === strtolower($request->input('email'));
                $phoneMatch = preg_replace('/\D/', '', $order->phone ?? '') === preg_replace('/\D/', '', $request->input('phone'));

                if ($emailMatch || $phoneMatch) {
                    $status = 'verified';
                    $verifiedAt = now();
                    $adminNotes = 'Automatically verified via internal Order match.';
                }
            }
        }

        // Calculate Warranty Period
        $warrantyMonths = (int) $this->getSetting('registry_warranty_duration_months', 24);
        $purchaseDate = \Carbon\Carbon::parse($request->input('purchase_date'));
        $warrantyStart = $purchaseDate->copy();
        $warrantyEnd = $purchaseDate->copy()->addMonths($warrantyMonths);

        // Generate Code and Save Registration
        $reg = ProductRegistration::create([
            'customer_name' => $request->input('customer_name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'whatsapp_number' => $request->input('whatsapp_number'),
            'purchase_source' => $request->input('purchase_source'),
            'order_or_bill_number' => $request->input('order_or_bill_number'),
            'purchase_date' => $purchaseDate->format('Y-m-d'),
            'product_id' => $request->input('product_id'),
            'product_name_snapshot' => $request->input('product_name_snapshot'),
            'serial_card_id' => $request->input('serial_card_id'),
            'source_store_name' => $request->input('source_store_name'),
            'source_city' => $request->input('source_city'),
            'invoice_file_path' => $invoicePath,
            'product_image_path' => $productImagePath,
            'notes' => $request->input('notes'),
            'whatsapp_opt_in' => $request->boolean('whatsapp_opt_in'),
            'verification_status' => $status,
            'warranty_start_date' => $warrantyStart->format('Y-m-d'),
            'warranty_end_date' => $warrantyEnd->format('Y-m-d'),
            'buyback_eligible' => $buybackEligible,
            'admin_notes' => $adminNotes,
            'verified_at' => $verifiedAt,
        ]);

        // Log Activity
        RegistrationActivityLog::create([
            'product_registration_id' => $reg->id,
            'action' => 'customer_registered',
            'new_data' => $reg->only(['registration_code', 'customer_name', 'email', 'verification_status']),
            'created_by' => 'customer'
        ]);

        // Send confirmation email
        $verifyStatusText = $status === 'verified' ? 'Verified & Active' : 'Pending Verification';
        $subject = "Ownership Guarantee Certificate Issued - {$reg->registration_code}";
        $body = "Dear {$reg->customer_name},\n\n" .
                "Thank you for registering your genuine handcrafted solid brass legacy piece with Little Divinity.\n\n" .
                "We have issued your Ownership Guarantee Certificate details below:\n" .
                "--------------------------------------------------\n" .
                "Guarantee Registration Code: {$reg->registration_code}\n" .
                "Product: {$reg->product_name_snapshot}\n" .
                "Purchase Source: " . str_replace('_', ' ', ucwords($reg->purchase_source)) . "\n" .
                "Order / Bill Number: {$reg->order_or_bill_number}\n" .
                "Purchase Date: {$reg->purchase_date->format('Y-m-d')}\n" .
                "Warranty Timeline: {$reg->warranty_start_date->format('Y-m-d')} to {$reg->warranty_end_date->format('Y-m-d')}\n" .
                "Verification Status: {$verifyStatusText}\n" .
                "--------------------------------------------------\n\n" .
                ($status === 'verified'
                    ? "Your website order has been automatically verified. Your structural guarantee is fully active."
                    : "For offline store, Amazon, or other marketplace purchases, our verification team will review your uploaded invoice document. Once verified, your active guarantee period will start and we will notify you immediately.") . "\n\n" .
                "To check registration status or claim repairs in the future, please visit: https://littledivinity.com/warranty-status?code={$reg->registration_code}\n\n" .
                "Warm regards,\n" .
                "Team Little Divinity\n" .
                "littledivinity.com";

        $this->sendNotificationMail($reg->email, $subject, $body);

        return response()->json([
            'success' => true,
            'message' => $status === 'verified'
                ? 'Your warranty has been automatically verified and activated successfully!'
                : 'Registration submitted successfully! Our team will verify your details soon.',
            'data' => [
                'registration_code' => $reg->registration_code,
                'verification_status' => $reg->verification_status,
                'warranty_start_date' => $reg->warranty_start_date->format('Y-m-d'),
                'warranty_end_date' => $reg->warranty_end_date->format('Y-m-d'),
            ]
        ]);
    }

    /**
     * Check Warranty & Buyback Status
     */
    public function getStatus(Request $request): JsonResponse
    {
        $code = $request->query('code');
        $orderNum = $request->query('order_number');
        $email = $request->query('email');
        $phone = $request->query('phone');

        $query = ProductRegistration::query();

        if ($code) {
            $query->where('registration_code', trim($code));
        } elseif ($orderNum) {
            $query->where('order_or_bill_number', trim($orderNum));
        } elseif ($email && $phone) {
            $query->where('email', trim($email))->where('phone', trim($phone));
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a registration code, order/bill number, or email and phone to lookup.'
            ], 420);
        }

        $registrations = $query->with(['claims', 'buybacks'])->get();

        if ($registrations->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No active guarantee or warranty registration found for the provided details.'
            ], 404);
        }

        $formatted = $registrations->map(function ($reg) {
            return [
                'registration_code' => $reg->registration_code,
                'product_name' => $reg->product_name_snapshot,
                'purchase_source' => $reg->purchase_source,
                'purchase_date' => $reg->purchase_date->format('Y-m-d'),
                'verification_status' => $reg->verification_status,
                'warranty_start' => $reg->warranty_start_date ? $reg->warranty_start_date->format('Y-m-d') : null,
                'warranty_end' => $reg->warranty_end_date ? $reg->warranty_end_date->format('Y-m-d') : null,
                'is_active' => $reg->verification_status === 'verified' && now()->between($reg->warranty_start_date, $reg->warranty_end_date),
                'buyback_eligible' => $reg->buyback_eligible && $reg->verification_status === 'verified',
                'claims' => $reg->claims->map(fn($c) => [
                    'claim_code' => $c->claim_code,
                    'issue_type' => $c->issue_type,
                    'status' => $c->status,
                    'created_at' => $c->created_at->format('Y-m-d H:i')
                ]),
                'buybacks' => $reg->buybacks->map(fn($b) => [
                    'request_code' => $b->request_code,
                    'status' => $b->status,
                    'estimated_value' => $b->estimated_buyback_value,
                    'final_value' => $b->final_buyback_value,
                    'created_at' => $b->created_at->format('Y-m-d H:i')
                ])
            ];
        });

        return response()->json([
            'success' => true,
            'registrations' => $formatted
        ]);
    }

    /**
     * Submit a Warranty Service Claim
     */
    public function submitClaim(Request $request): JsonResponse
    {
        $allowedFileTypes = explode(',', $this->getSetting('registry_allowed_file_types', 'pdf,jpg,jpeg,png,webp'));
        $maxSizeMb = (int) $this->getSetting('registry_allowed_upload_size_mb', 5);

        $validator = Validator::make($request->all(), [
            'registration_code' => ['required', 'string', 'exists:product_registrations,registration_code'],
            'issue_type' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'min:10'],
            'images' => ['nullable', 'array'],
            'images.*' => [
                'file',
                'max:' . ($maxSizeMb * 1024),
                function ($attribute, $value, $fail) use ($allowedFileTypes) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, $allowedFileTypes) || $ext === 'pdf') {
                        $fail("All issue photos must be valid images (jpg, jpeg, png, webp).");
                    }
                }
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $reg = ProductRegistration::where('registration_code', $request->input('registration_code'))->firstOrFail();

        if ($reg->verification_status !== 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'A warranty claim can only be submitted for verified and active registrations. Please wait for our team to verify your warranty.'
            ], 403);
        }

        // Handle uploads
        $imageUrls = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('registry/claims', 'public');
                $imageUrls[] = Storage::disk('public')->url($path);
            }
        }

        $claim = WarrantyClaim::create([
            'product_registration_id' => $reg->id,
            'issue_type' => $request->input('issue_type'),
            'description' => $request->input('description'),
            'image_paths' => $imageUrls,
            'status' => 'submitted'
        ]);

        // Log Activity
        RegistrationActivityLog::create([
            'product_registration_id' => $reg->id,
            'action' => 'claim_submitted',
            'new_data' => $claim->only(['claim_code', 'issue_type', 'status']),
            'created_by' => 'customer'
        ]);

        // Send claim email
        $subject = "Guarantee Service Request Received - {$claim->claim_code}";
        $body = "Dear {$reg->customer_name},\n\n" .
                "We have successfully received your guarantee service request ticket for your product: {$reg->product_name_snapshot}.\n\n" .
                "Claim Details:\n" .
                "--------------------------------------------------\n" .
                "Service Claim Code: {$claim->claim_code}\n" .
                "Associated Registry Code: {$reg->registration_code}\n" .
                "Service Request Type: " . str_replace('_', ' ', ucwords($claim->issue_type)) . "\n" .
                "Status: SUBMITTED\n" .
                "--------------------------------------------------\n\n" .
                "A Little Divinity support representative will inspect the uploaded issue details, review the photos, and contact you via email or WhatsApp within 24-48 business hours to guide you on repair shipping or servicing actions.\n\n" .
                "To track your service ticket status, please visit: https://littledivinity.com/warranty-status\n\n" .
                "Warm regards,\n" .
                "Team Little Divinity\n" .
                "littledivinity.com";

        $this->sendNotificationMail($reg->email, $subject, $body);

        return response()->json([
            'success' => true,
            'message' => 'Warranty service claim submitted successfully! Our support representative will review the case soon.',
            'claim_code' => $claim->claim_code
        ]);
    }

    /**
     * Submit a Buyback Request
     */
    public function submitBuyback(Request $request): JsonResponse
    {
        if ($this->getSetting('registry_allow_buyback', '1') !== '1') {
            return response()->json([
                'success' => false,
                'message' => 'Buyback requests are temporarily disabled by the store manager.'
            ], 403);
        }

        $allowedFileTypes = explode(',', $this->getSetting('registry_allowed_file_types', 'pdf,jpg,jpeg,png,webp'));
        $maxSizeMb = (int) $this->getSetting('registry_allowed_upload_size_mb', 5);

        $validator = Validator::make($request->all(), [
            'registration_code' => ['required', 'string', 'exists:product_registrations,registration_code'],
            'condition_notes' => ['required', 'string', 'min:10'],
            'pickup_city' => ['required', 'string', 'max:150'],
            'preferred_contact_method' => ['required', 'string', 'in:whatsapp,phone,email'],
            'images' => ['required', 'array', 'min:1'],
            'images.*' => [
                'file',
                'max:' . ($maxSizeMb * 1024),
                function ($attribute, $value, $fail) use ($allowedFileTypes) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, $allowedFileTypes) || $ext === 'pdf') {
                        $fail("All condition photos must be valid images (jpg, jpeg, png, webp).");
                    }
                }
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $reg = ProductRegistration::where('registration_code', $request->input('registration_code'))->firstOrFail();

        if ($reg->verification_status !== 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'A buyback evaluation request can only be submitted for verified registrations.'
            ], 403);
        }

        if (!$reg->buyback_eligible) {
            return response()->json([
                'success' => false,
                'message' => 'This product registration is marked as ineligible for buyback programs. Please check details or contact customer care.'
            ], 403);
        }

        // Handle uploads
        $imageUrls = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('registry/buybacks', 'public');
                $imageUrls[] = Storage::disk('public')->url($path);
            }
        }

        $buyback = BuybackRequest::create([
            'product_registration_id' => $reg->id,
            'condition_notes' => $request->input('condition_notes'),
            'pickup_city' => $request->input('pickup_city'),
            'preferred_contact_method' => $request->input('preferred_contact_method'),
            'image_paths' => $imageUrls,
            'status' => 'submitted'
        ]);

        // Log Activity
        RegistrationActivityLog::create([
            'product_registration_id' => $reg->id,
            'action' => 'buyback_requested',
            'new_data' => $buyback->only(['request_code', 'pickup_city', 'status']),
            'created_by' => 'customer'
        ]);

        // Send buyback email
        $subject = "Vault Return-to-Vault Buyback Appraisal Submitted - {$buyback->request_code}";
        $body = "Dear {$reg->customer_name},\n\n" .
                "We have successfully received your Return-to-Vault buyback appraisal ticket for your brassware: {$reg->product_name_snapshot}.\n\n" .
                "Appraisal Details:\n" .
                "--------------------------------------------------\n" .
                "Request Code: {$buyback->request_code}\n" .
                "Associated Registry Code: {$reg->registration_code}\n" .
                "Pickup City: {$buyback->pickup_city}\n" .
                "Preferred Contact: " . ucwords($buyback->preferred_contact_method) . "\n" .
                "Status: SUBMITTED (INSPECTION PENDING)\n" .
                "--------------------------------------------------\n\n" .
                "Our official brass appraiser will inspect your uploaded condition photos, calculate current solid brass metal valuation ratios, and get in touch with you via your preferred contact channel within 48 business hours to propose a valuation estimate.\n\n" .
                "To track your vault return ticket status, please visit: https://littledivinity.com/warranty-status\n\n" .
                "Warm regards,\n" .
                "Team Little Divinity\n" .
                "littledivinity.com";

        $this->sendNotificationMail($reg->email, $subject, $body);

        return response()->json([
            'success' => true,
            'message' => 'Buyback evaluation request submitted successfully! Our appraiser will inspect photos and propose a trade-in / return-to-vault valuation.',
            'request_code' => $buyback->request_code
        ]);
    }

    /**
     * Send email helper using standard SMTP settings template
     */
    private function sendNotificationMail(string $email, string $subject, string $body): void
    {
        $settings = CustomerEmailSetting::query()->first();

        if (!$settings || !$settings->is_active) {
            return;
        }

        $smtpPassword = $settings->smtp_password;
        $smtpScheme = match (strtolower((string) $settings->smtp_encryption)) {
            'ssl' => 'smtps',
            'tls' => 'tls',
            default => null,
        };

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $settings->smtp_host,
            'mail.mailers.smtp.port' => $settings->smtp_port,
            'mail.mailers.smtp.scheme' => $smtpScheme,
            'mail.mailers.smtp.encryption' => $settings->smtp_encryption,
            'mail.mailers.smtp.username' => $settings->smtp_username,
            'mail.mailers.smtp.password' => $smtpPassword,
            'mail.from.address' => $settings->from_email,
            'mail.from.name' => $settings->from_name ?: 'Little Divinity',
        ]);

        try {
            Mail::raw($body, function ($message) use ($email, $settings, $subject): void {
                $message->to($email)
                    ->from($settings->from_email, $settings->from_name ?: 'Little Divinity')
                    ->replyTo($settings->reply_to_email ?: $settings->from_email)
                    ->subject($subject);
            });
        } catch (\Throwable $throwable) {
            Log::error("Registry notification email failed to send to {$email}: " . $throwable->getMessage());
        }
    }
}
