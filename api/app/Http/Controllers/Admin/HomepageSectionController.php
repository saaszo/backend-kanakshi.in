<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesAdminUploads;
use App\Http\Controllers\Controller;
use App\Models\HomepageSection;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomepageSectionController extends Controller
{
    use HandlesAdminUploads;

    public function index(): View
    {
        return view('admin.homepage-sections.index', [
            'sections' => HomepageSection::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function edit(HomepageSection $homepageSection): View
    {
        return view('admin.homepage-sections.edit', [
            'section' => $homepageSection,
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function update(Request $request, HomepageSection $homepageSection): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:150'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'heading' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'button_text' => ['nullable', 'string', 'max:120'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'string', 'max:255'],
            'image_file' => ['nullable', 'image', 'max:5120'],
            'mobile_image_url' => ['nullable', 'string', 'max:255'],
            'mobile_image_file' => ['nullable', 'image', 'max:5120'],
            'side_image_url' => ['nullable', 'string', 'max:255'],
            'side_image_file' => ['nullable', 'image', 'max:5120'],
            'side_secondary_image_url' => ['nullable', 'string', 'max:255'],
            'side_secondary_image_file' => ['nullable', 'image', 'max:5120'],
            'sort_order' => ['required', 'integer'],
            'config_json' => ['nullable', 'string'],
        ]);

        $config = [];
        if (! empty($validated['config_json'])) {
            $config = json_decode($validated['config_json'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors([
                    'config_json' => 'Config JSON is invalid.',
                ])->withInput();
            }
        }

        unset($validated['config_json']);

        if ($request->hasFile('image_file')) {
            $validated['image_url'] = $this->storeAdminUpload($request->file('image_file'), 'homepage', 'Homepage main image');
        }
        if ($request->hasFile('mobile_image_file')) {
            $validated['mobile_image_url'] = $this->storeAdminUpload($request->file('mobile_image_file'), 'homepage', 'Homepage mobile image');
        }
        if ($request->hasFile('side_image_file')) {
            $validated['side_image_url'] = $this->storeAdminUpload($request->file('side_image_file'), 'homepage', 'Homepage side image');
        }
        if ($request->hasFile('side_secondary_image_file')) {
            $validated['side_secondary_image_url'] = $this->storeAdminUpload($request->file('side_secondary_image_file'), 'homepage', 'Homepage secondary side image');
        }

        $homepageSection->update($validated + [
            'is_active' => $request->boolean('is_active'),
            'config' => $config,
        ]);

        return back()->with('status', 'Homepage section updated successfully.');
    }
}
