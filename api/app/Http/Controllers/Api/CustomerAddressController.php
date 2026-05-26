<?php

namespace App\Http\Controllers\Api;

use App\Models\CustomerAccessToken;
use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerAddressController
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveCustomerFromRequest($request);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized customer session.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Customer addresses fetched successfully.',
            'data' => [
                'addresses' => $this->serializeAddresses($user),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $this->resolveCustomerFromRequest($request);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized customer session.',
            ], 401);
        }

        $validated = $this->validateAddress($request);
        $makeDefault = (bool) ($validated['is_default'] ?? false) || ! $user->addresses()->exists();

        if ($makeDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create([
            ...$validated,
            'recipient_name' => trim($validated['recipient_name']),
            'phone' => isset($validated['phone']) ? trim($validated['phone']) : null,
            'address_line1' => trim($validated['address_line1']),
            'address_line2' => isset($validated['address_line2']) ? trim($validated['address_line2']) : null,
            'city' => trim($validated['city']),
            'state' => trim($validated['state']),
            'pincode' => trim($validated['pincode']),
            'landmark' => isset($validated['landmark']) ? trim($validated['landmark']) : null,
            'label' => isset($validated['label']) ? trim($validated['label']) : null,
            'is_default' => $makeDefault,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Address added successfully.',
            'data' => [
                'address' => $this->serializeAddress($address),
                'addresses' => $this->serializeAddresses($user->fresh()),
            ],
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $this->resolveCustomerFromRequest($request);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized customer session.',
            ], 401);
        }

        $address = $user->addresses()->find($id);

        if (! $address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found.',
            ], 404);
        }

        $validated = $this->validateAddress($request);
        $makeDefault = (bool) ($validated['is_default'] ?? false);

        if ($makeDefault) {
            $user->addresses()
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->fill([
            ...$validated,
            'recipient_name' => trim($validated['recipient_name']),
            'phone' => isset($validated['phone']) ? trim($validated['phone']) : null,
            'address_line1' => trim($validated['address_line1']),
            'address_line2' => isset($validated['address_line2']) ? trim($validated['address_line2']) : null,
            'city' => trim($validated['city']),
            'state' => trim($validated['state']),
            'pincode' => trim($validated['pincode']),
            'landmark' => isset($validated['landmark']) ? trim($validated['landmark']) : null,
            'label' => isset($validated['label']) ? trim($validated['label']) : null,
            'is_default' => $makeDefault || ($address->is_default && ! $makeDefault),
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully.',
            'data' => [
                'address' => $this->serializeAddress($address->fresh()),
                'addresses' => $this->serializeAddresses($user->fresh()),
            ],
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $this->resolveCustomerFromRequest($request);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized customer session.',
            ], 401);
        }

        $address = $user->addresses()->find($id);

        if (! $address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found.',
            ], 404);
        }

        $deletedDefault = $address->is_default;
        $address->delete();

        if ($deletedDefault) {
            $replacement = $user->addresses()->latest('id')->first();
            if ($replacement) {
                $replacement->forceFill(['is_default' => true])->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Address removed successfully.',
            'data' => [
                'addresses' => $this->serializeAddresses($user->fresh()),
            ],
        ]);
    }

    private function resolveCustomerFromRequest(Request $request): ?User
    {
        $token = $this->resolveTokenModelFromRequest($request);

        if (! $token) {
            return null;
        }

        $token->forceFill([
            'last_used_at' => now(),
        ])->save();

        return $token->user;
    }

    private function resolveTokenModelFromRequest(Request $request): ?CustomerAccessToken
    {
        $bearer = $request->bearerToken();

        if (! $bearer) {
            return null;
        }

        return CustomerAccessToken::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $bearer))
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'string', 'in:home,office,other'],
            'label' => ['nullable', 'string', 'max:60'],
            'recipient_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'max:12'],
            'landmark' => ['nullable', 'string', 'max:150'],
            'is_default' => ['nullable', 'boolean'],
        ]);
    }

    private function serializeAddresses(User $user): array
    {
        return $user->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (CustomerAddress $address) => $this->serializeAddress($address))
            ->all();
    }

    private function serializeAddress(CustomerAddress $address): array
    {
        return [
            'id' => $address->id,
            'type' => $address->type,
            'label' => $address->label,
            'recipient_name' => $address->recipient_name,
            'phone' => $address->phone,
            'address_line1' => $address->address_line1,
            'address_line2' => $address->address_line2,
            'city' => $address->city,
            'state' => $address->state,
            'pincode' => $address->pincode,
            'landmark' => $address->landmark,
            'is_default' => (bool) $address->is_default,
            'created_at' => optional($address->created_at)?->toIso8601String(),
            'updated_at' => optional($address->updated_at)?->toIso8601String(),
        ];
    }
}
