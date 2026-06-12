<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    private const LOCATION_ORDER_SQL = "CASE location WHEN 'header' THEN 1 WHEN 'footer' THEN 2 WHEN 'mobile' THEN 3 ELSE 99 END";

    public function index(): View
    {
        $menuItems = MenuItem::query()
            ->with('parent:id,title')
            ->withCount('children')
            ->orderByRaw(self::LOCATION_ORDER_SQL)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('admin.menu-items.index', [
            'menuItems' => $menuItems,
            'groupedMenuItems' => $menuItems->groupBy('location'),
            'parents' => MenuItem::query()
                ->whereNull('parent_id')
                ->orderByRaw(self::LOCATION_ORDER_SQL)
                ->orderBy('title')
                ->get(['id', 'title', 'location']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);
        $config = $this->decodeConfig($validated['config_json'] ?? null);
        unset($validated['config_json']);

        MenuItem::query()->create($this->normalizePayload($validated) + [
            'is_active' => $request->boolean('is_active', true),
            'config' => $config,
        ]);

        return back()->with('status', 'Menu item added successfully.');
    }

    public function update(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $validated = $this->validatePayload($request);
        $config = $this->decodeConfig($validated['config_json'] ?? null);
        unset($validated['config_json']);

        $menuItem->update($this->normalizePayload($validated) + [
            'is_active' => $request->boolean('is_active'),
            'config' => $config,
        ]);

        return back()->with('status', 'Menu item updated successfully.');
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        MenuItem::query()
            ->where('parent_id', $menuItem->id)
            ->update([
                'parent_id' => null,
                'updated_at' => now(),
            ]);

        $menuItem->delete();

        return back()->with('status', 'Menu item removed successfully.');
    }

    private function validatePayload(Request $request): array
    {
        $validated = $request->validate([
            'location' => ['required', 'in:header,footer,mobile'],
            'title' => ['required', 'string', 'max:150'],
            'url' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:menu_items,id'],
            'target' => ['nullable', 'string', 'max:20'],
            'css_class' => ['nullable', 'string', 'max:120'],
            'icon' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer'],
            'config_json' => ['nullable', 'string'],
        ]);

        if (! empty($validated['parent_id'])) {
            $parent = MenuItem::query()->find($validated['parent_id']);

            if ($parent && $parent->location !== $validated['location']) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Parent item must be from the same menu location.',
                ]);
            }

            if ($parent && $parent->parent_id) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Please choose a top-level menu item as the parent.',
                ]);
            }
        }

        return $validated;
    }

    private function normalizePayload(array $validated): array
    {
        return array_merge($validated, [
            'parent_id' => filled($validated['parent_id'] ?? null) ? (int) $validated['parent_id'] : null,
            'target' => filled($validated['target'] ?? null) ? $validated['target'] : '_self',
            'css_class' => filled($validated['css_class'] ?? null) ? $validated['css_class'] : null,
            'icon' => filled($validated['icon'] ?? null) ? $validated['icon'] : null,
            'sort_order' => filled($validated['sort_order'] ?? null) ? (int) $validated['sort_order'] : 0,
        ]);
    }

    private function decodeConfig(?string $configJson): array
    {
        if (! $configJson) {
            return [];
        }

        $decoded = json_decode($configJson, true);

        return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : [];
    }
}
