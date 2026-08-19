<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ContactInquiryController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $query = DB::table('contact_inquiries')->orderByDesc('created_at');

        if ($status && in_array($status, ['pending', 'contacted', 'resolved'], true)) {
            $query->where('status', $status);
        }

        $inquiries = $query->paginate(20)->withQueryString();
        $counts = [
            'total' => DB::table('contact_inquiries')->count(),
            'pending' => DB::table('contact_inquiries')->where('status', 'pending')->count(),
            'contacted' => DB::table('contact_inquiries')->where('status', 'contacted')->count(),
            'resolved' => DB::table('contact_inquiries')->where('status', 'resolved')->count(),
        ];

        return view('admin.inquiries.index', compact('inquiries', 'counts', 'status'));
    }

    public function show(int $id): View
    {
        $inquiry = DB::table('contact_inquiries')->where('id', $id)->first();
        if (!$inquiry) {
            abort(404, 'Contact inquiry not found.');
        }

        return view('admin.inquiries.show', compact('inquiry'));
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,contacted,resolved'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::table('contact_inquiries')->where('id', $id)->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? null,
            'updated_at' => now(),
        ]);

        return back()->with('status', 'Inquiry status updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        DB::table('contact_inquiries')->where('id', $id)->delete();

        return redirect()->route('admin.inquiries.index')->with('status', 'Inquiry deleted successfully.');
    }
}
