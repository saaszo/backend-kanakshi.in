<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'products' => Product::query()->count(),
                'categories' => Category::query()->count(),
                'homepage_sections' => HomepageSection::query()->count(),
                'admins' => User::query()->whereIn('role', ['super_admin', 'admin', 'manager', 'staff'])->count(),
            ],
        ]);
    }
}
