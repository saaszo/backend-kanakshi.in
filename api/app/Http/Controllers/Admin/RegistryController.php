<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductRegistration;
use App\Models\WarrantyClaim;
use App\Models\BuybackRequest;
use App\Models\RegistrationActivityLog;
use App\Models\Setting;
use App\Services\CustomerEmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class RegistryController extends Controller
{
    private function getSetting(string $key, mixed $default = null): mixed
    {
        $setting = Setting::query()->where('key_name', $key)->first();
        return $setting ? $setting->value : $default;
    }

    private function setSetting(string $key, mixed $value, string $label = '', string $group = 'registry'): void
    {
        Setting::query()->updateOrCreate(
            ['key_name' => $key],
            ['value' => $value, 'label' => $label, 'group_name' => $group]
        );
    }

    /**
     * 1. Registrations List
     */
    public function index(Request $request): View
    {
        $source = trim((string) $request->string('source'));
        $status = trim((string) $request->string('status'));
        $buybackEligible = $request->query('buyback_eligible');
        $search = trim((string) $request->string('q'));

        $query = ProductRegistration::query();

        if ($source !== '') {
            $query->where('purchase_source', $source);
        }

        if ($status !== '') {
            $query->where('verification_status', $status);
        }

        if ($buybackEligible !== null && $buybackEligible !== '') {
            $query->where('buyback_eligible', $buybackEligible);
        }

        if ($search !== '') {
            $term = '%' . $search . '%';
            $query->where(function ($builder) use ($term) {
                $builder->where('registration_code', 'like', $term)
                    ->orWhere('customer_name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('order_or_bill_number', 'like', $term);
            });
        }

        return view('admin.registry.registrations.index', [
            'registrations' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => [
                'source' => $source,
                'status' => $status,
                'buyback_eligible' => $buybackEligible,
                'q' => $search,
            ]
        ]);
    }

    /**
     * View Detailed Registration
     */
    public function show(ProductRegistration $registration): View
    {
        $registration->load(['claims', 'buybacks', 'activityLogs']);
        return view('admin.registry.registrations.show', [
            'reg' => $registration
        ]);
    }

    /**
     * Verify/Approve Registration
     */
    public function verify(ProductRegistration $registration, Request $request): RedirectResponse
    {
        $adminEmail = Auth::user()?->email ?: 'admin';

        $validated = $request->validate([
            'warranty_start_date' => ['required', 'date'],
            'warranty_end_date' => ['required', 'date', 'after:warranty_start_date'],
            'admin_notes' => ['nullable', 'string']
        ]);

        DB::transaction(function () use ($registration, $validated, $adminEmail) {
            $oldData = $registration->only(['verification_status', 'warranty_start_date', 'warranty_end_date', 'admin_notes']);

            $registration->update([
                'verification_status' => 'verified',
                'warranty_start_date' => $validated['warranty_start_date'],
                'warranty_end_date' => $validated['warranty_end_date'],
                'admin_notes' => $validated['admin_notes'] ?? $registration->admin_notes,
                'verified_at' => now(),
            ]);

            RegistrationActivityLog::create([
                'product_registration_id' => $registration->id,
                'action' => 'admin_verified',
                'old_data' => $oldData,
                'new_data' => $registration->only(['verification_status', 'warranty_start_date', 'warranty_end_date', 'admin_notes']),
                'created_by' => $adminEmail
            ]);
        });

        $registration->refresh();
        // Send email notification
        $subject = "Guarantee Registration Certificate Verified - {$registration->registration_code}";
        $body = "Dear {$registration->customer_name},\n\n" .
                "We are pleased to inform you that your ownership registry details for your legacy piece \"{$registration->product_name_snapshot}\" have been verified and approved!\n\n" .
                "Your 2-Year structural guarantee coverage is now fully active.\n\n" .
                "Guarantee Details:\n" .
                "--------------------------------------------------\n" .
                "Guarantee Registration Code: {$registration->registration_code}\n" .
                "Product: {$registration->product_name_snapshot}\n" .
                "Warranty Timeline: " . $registration->warranty_start_date->format('Y-m-d') . " to " . $registration->warranty_end_date->format('Y-m-d') . "\n" .
                "Verification Status: VERIFIED & ACTIVE\n" .
                "--------------------------------------------------\n\n" .
                ($validated['admin_notes'] ? "Assessing Officer Notes: {$validated['admin_notes']}\n\n" : "") .
                "To view certificate status or file repair claims in the future, please visit: https://littledivinity.com/warranty-status?code={$registration->registration_code}\n\n" .
                "Warm regards,\n" .
                "Team Little Divinity\n" .
                "littledivinity.com";

        $this->sendNotificationMail($registration->email, $subject, $body);

        return back()->with('status', 'Guarantee registration approved and warranty activated successfully.');
    }

    /**
     * Reject Registration
     */
    public function reject(ProductRegistration $registration, Request $request): RedirectResponse
    {
        $adminEmail = Auth::user()?->email ?: 'admin';

        $validated = $request->validate([
            'admin_notes' => ['required', 'string', 'min:3']
        ]);

        DB::transaction(function () use ($registration, $validated, $adminEmail) {
            $oldData = $registration->only(['verification_status', 'admin_notes']);

            $registration->update([
                'verification_status' => 'rejected',
                'admin_notes' => $validated['admin_notes'],
                'rejected_at' => now(),
            ]);

            RegistrationActivityLog::create([
                'product_registration_id' => $registration->id,
                'action' => 'admin_rejected',
                'old_data' => $oldData,
                'new_data' => $registration->only(['verification_status', 'admin_notes']),
                'created_by' => $adminEmail
            ]);
        });

        // Send email notification
        $subject = "Guarantee Registration Status Update - {$registration->registration_code}";
        $body = "Dear {$registration->customer_name},\n\n" .
                "We have reviewed your recent guarantee registry application for registration code: {$registration->registration_code}.\n\n" .
                "Unfortunately, our verification team was unable to verify your application at this time.\n\n" .
                "Verification Status: REJECTED\n" .
                "Reason for Rejection:\n" .
                "--------------------------------------------------\n" .
                "{$validated['admin_notes']}\n" .
                "--------------------------------------------------\n\n" .
                "If you believe this was an error or you have a corrected invoice to upload, please register again at: https://littledivinity.com/warranty-portal or contact customer care.\n\n" .
                "Warm regards,\n" .
                "Team Little Divinity\n" .
                "littledivinity.com";

        $this->sendNotificationMail($registration->email, $subject, $body);

        return back()->with('status', 'Guarantee registration rejected.');
    }

    /**
     * Edit notes or buyback eligibility
     */
    public function updateNotes(ProductRegistration $registration, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string'],
            'buyback_eligible' => ['required', 'boolean']
        ]);

        $registration->update([
            'admin_notes' => $validated['admin_notes'],
            'buyback_eligible' => $validated['buyback_eligible']
        ]);

        return back()->with('status', 'Registration notes updated successfully.');
    }

    /**
     * 2. Claims List
     */
    public function claimsIndex(Request $request): View
    {
        $status = trim((string) $request->string('status'));
        $search = trim((string) $request->string('q'));

        $query = WarrantyClaim::query()->with('registration');

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $term = '%' . $search . '%';
            $query->where(function ($builder) use ($term) {
                $builder->where('claim_code', 'like', $term)
                    ->orWhere('issue_type', 'like', $term)
                    ->orWhereHas('registration', function ($regQuery) use ($term) {
                        $regQuery->where('registration_code', 'like', $term)
                            ->orWhere('customer_name', 'like', $term)
                            ->orWhere('product_name_snapshot', 'like', $term);
                    });
            });
        }

        return view('admin.registry.claims.index', [
            'claims' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => [
                'status' => $status,
                'q' => $search
            ]
        ]);
    }

    /**
     * View Warranty Claim Details
     */
    public function claimShow(WarrantyClaim $claim): View
    {
        $claim->load('registration.activityLogs');
        return view('admin.registry.claims.show', [
            'claim' => $claim
        ]);
    }

    /**
     * Update Claim Status
     */
    public function updateClaim(Request $request, WarrantyClaim $claim): RedirectResponse
    {
        $adminEmail = Auth::user()?->email ?: 'admin';

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:submitted,under_review,approved,rejected,in_service,completed'],
            'admin_notes' => ['nullable', 'string']
        ]);

        DB::transaction(function () use ($claim, $validated, $adminEmail) {
            $oldStatus = $claim->status;

            $claim->update([
                'status' => $validated['status'],
                'admin_notes' => $validated['admin_notes'] ?? $claim->admin_notes,
                'resolved_at' => in_array($validated['status'], ['completed', 'rejected']) ? now() : $claim->resolved_at
            ]);

            if ($oldStatus !== $validated['status']) {
                RegistrationActivityLog::create([
                    'product_registration_id' => $claim->product_registration_id,
                    'action' => 'claim_status_updated',
                    'old_data' => ['status' => $oldStatus],
                    'new_data' => $claim->only(['claim_code', 'status', 'admin_notes']),
                    'created_by' => $adminEmail
                ]);
            }
        });

        return back()->with('status', 'Warranty claim status updated successfully.');
    }

    /**
     * 3. Buyback Requests List
     */
    public function buybacksIndex(Request $request): View
    {
        $status = trim((string) $request->string('status'));
        $search = trim((string) $request->string('q'));

        $query = BuybackRequest::query()->with('registration');

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $term = '%' . $search . '%';
            $query->where(function ($builder) use ($term) {
                $builder->where('request_code', 'like', $term)
                    ->orWhereHas('registration', function ($regQuery) use ($term) {
                        $regQuery->where('registration_code', 'like', $term)
                            ->orWhere('customer_name', 'like', $term)
                            ->orWhere('product_name_snapshot', 'like', $term);
                    });
            });
        }

        return view('admin.registry.buybacks.index', [
            'buybacks' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => [
                'status' => $status,
                'q' => $search
            ]
        ]);
    }

    /**
     * View Buyback Request Details
     */
    public function buybackShow(BuybackRequest $buyback): View
    {
        $buyback->load('registration.activityLogs');
        return view('admin.registry.buybacks.show', [
            'buyback' => $buyback
        ]);
    }

    /**
     * Update Buyback Evaluation Details
     */
    public function updateBuyback(Request $request, BuybackRequest $buyback): RedirectResponse
    {
        $adminEmail = Auth::user()?->email ?: 'admin';

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:submitted,inspection_pending,valued,approved,rejected,completed'],
            'estimated_buyback_value' => ['nullable', 'numeric', 'min:0'],
            'final_buyback_value' => ['nullable', 'numeric', 'min:0'],
            'admin_notes' => ['nullable', 'string']
        ]);

        DB::transaction(function () use ($buyback, $validated, $adminEmail) {
            $oldStatus = $buyback->status;

            $buyback->update([
                'status' => $validated['status'],
                'estimated_buyback_value' => $validated['estimated_buyback_value'] ?? $buyback->estimated_buyback_value,
                'final_buyback_value' => $validated['final_buyback_value'] ?? $buyback->final_buyback_value,
                'admin_notes' => $validated['admin_notes'] ?? $buyback->admin_notes,
                'approved_at' => $validated['status'] === 'approved' ? now() : $buyback->approved_at,
                'completed_at' => $validated['status'] === 'completed' ? now() : $buyback->completed_at,
            ]);

            if ($oldStatus !== $validated['status']) {
                RegistrationActivityLog::create([
                    'product_registration_id' => $buyback->product_registration_id,
                    'action' => 'buyback_status_updated',
                    'old_data' => ['status' => $oldStatus],
                    'new_data' => $buyback->only(['request_code', 'status', 'estimated_buyback_value', 'final_buyback_value', 'admin_notes']),
                    'created_by' => $adminEmail
                ]);
            }
        });

        return back()->with('status', 'Buyback request evaluation details updated successfully.');
    }

    /**
     * 4. Settings Screen (Edit)
     */
    public function editSettings(): View
    {
        return view('admin.registry.settings', [
            'warrantyDuration' => $this->getSetting('registry_warranty_duration_months', '24'),
            'allowBuyback' => $this->getSetting('registry_allow_buyback', '1') === '1',
            'allowedSources' => json_decode($this->getSetting('registry_allowed_sources', '["website","offline_store","amazon","other_marketplace"]'), true) ?: [],
            'allowedUploadSize' => $this->getSetting('registry_allowed_upload_size_mb', '5'),
            'allowedFileTypes' => $this->getSetting('registry_allowed_file_types', 'pdf,jpg,jpeg,png,webp'),
            'autoVerifyWebsiteOrders' => $this->getSetting('registry_auto_verify_website_orders', '1') === '1',
        ]);
    }

    /**
     * Settings Screen (Update)
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'registry_warranty_duration_months' => ['required', 'integer', 'min:1'],
            'registry_allow_buyback' => ['required', 'boolean'],
            'registry_allowed_sources' => ['required', 'array'],
            'registry_allowed_sources.*' => ['string', 'in:website,offline_store,amazon,other_marketplace'],
            'registry_allowed_upload_size_mb' => ['required', 'integer', 'min:1', 'max:50'],
            'registry_allowed_file_types' => ['required', 'string'],
            'registry_auto_verify_website_orders' => ['required', 'boolean'],
        ]);

        $this->setSetting('registry_warranty_duration_months', $validated['registry_warranty_duration_months'], 'Warranty Duration (Months)');
        $this->setSetting('registry_allow_buyback', $validated['registry_allow_buyback'] ? '1' : '0', 'Allow Buyback Programs');
        $this->setSetting('registry_allowed_sources', json_encode($validated['registry_allowed_sources']), 'Allowed Purchase Sources');
        $this->setSetting('registry_allowed_upload_size_mb', $validated['registry_allowed_upload_size_mb'], 'Max File Upload Size (MB)');
        $this->setSetting('registry_allowed_file_types', strtolower(str_replace(' ', '', $validated['registry_allowed_file_types'])), 'Allowed Upload File Extensions');
        $this->setSetting('registry_auto_verify_website_orders', $validated['registry_auto_verify_website_orders'] ? '1' : '0', 'Auto Verify Internal Website Orders');

        return back()->with('status', 'Guarantee registry configurations updated successfully.');
    }

    /**
     * Send email helper using standard SMTP settings template
     */
    private function sendNotificationMail(string $email, string $subject, string $body): void
    {
        try {
            $service = app(CustomerEmailService::class);

            if (! $service->canSendOrderEmails()) {
                return;
            }

            $service->sendOrderMail($email, $subject, $body);
        } catch (\Throwable $throwable) {
            Log::error("Registry admin action notification email failed to send to {$email}: " . $throwable->getMessage());
        }
    }
}
