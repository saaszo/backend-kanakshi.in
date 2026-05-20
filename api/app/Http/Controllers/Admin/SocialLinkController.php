<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SocialLinkController extends Controller
{
    public function index(): View
    {
        return view('admin.social-links.index', [
            'socialLinks' => SocialLink::query()->orderBy('sort_order')->orderBy('platform')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'platform' => ['required', 'string', 'max:50'],
            'title' => ['nullable', 'string', 'max:100'],
            'handle' => ['nullable', 'string', 'max:120'],
            'url' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        SocialLink::query()->create($validated + [
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', 'Social link added successfully.');
    }

    public function update(Request $request, SocialLink $socialLink): RedirectResponse
    {
        $validated = $request->validate([
            'platform' => ['required', 'string', 'max:50'],
            'title' => ['nullable', 'string', 'max:100'],
            'handle' => ['nullable', 'string', 'max:120'],
            'url' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $socialLink->update($validated + [
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'Social link updated successfully.');
    }

    public function destroy(SocialLink $socialLink): RedirectResponse
    {
        $socialLink->delete();

        return back()->with('status', 'Social link removed successfully.');
    }
}
