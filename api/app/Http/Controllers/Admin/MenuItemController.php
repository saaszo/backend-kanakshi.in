<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function index(): View
    {
        return view('admin.menu-items.index', [
            'menuItems' => MenuItem::query()->orderBy('location')->orderBy('sort_order')->orderBy('title')->get(),
            'parents' => MenuItem::query()->orderBy('location')->orderBy('title')->get(['id', 'title', 'location']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);
        $config = $this->decodeConfig($validated['config_json'] ?? null);
        unset($validated['config_json']);

        MenuItem::query()->create($validated + [
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

        $menuItem->update($validated + [
            'is_active' => $request->boolean('is_active'),
            'config' => $config,
        ]);

        return back()->with('status', 'Menu item updated successfully.');
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->delete();

        return back()->with('status', 'Menu item removed successfully.');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
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
