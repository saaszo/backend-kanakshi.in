<?php

namespace App\Http\Controllers\Admin;

use App\Models\Auction;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuctionController
{
    public function index(): View
    {
        $auctions = Auction::with('winner', 'createdBy')
            ->orderByDesc('created_at')
            ->get();

        // Sync statuses for all non-cancelled auctions
        foreach ($auctions as $auction) {
            $auction->syncStatus();
        }

        $auctions = Auction::with('winner')->orderByDesc('created_at')->get();

        return view('admin.auctions.index', compact('auctions'));
    }

    public function create(): View
    {
        $products = Product::orderBy('name')->get();
        return view('admin.auctions.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'             => ['required', 'string', 'max:255'],
            'product_id'        => ['nullable', 'exists:products,id'],
            'image_url'         => ['nullable', 'url', 'max:2048'],
            'start_price'       => ['required', 'numeric', 'min:0'],
            'reserve_price'     => ['nullable', 'numeric', 'min:0'],
            'min_bid_increment' => ['required', 'numeric', 'min:1'],
            'start_at'          => ['required', 'date', 'after:now'],
            'end_at'            => ['required', 'date', 'after:start_at'],
            'description'       => ['nullable', 'string'],
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'draft';

        Auction::create($validated);

        return redirect()->route('admin.auctions.index')
            ->with('status', 'Auction created successfully.');
    }

    public function edit(Auction $auction): View
    {
        $products = Product::orderBy('name')->get();
        return view('admin.auctions.edit', compact('auction', 'products'));
    }

    public function update(Request $request, Auction $auction): RedirectResponse
    {
        $validated = $request->validate([
            'title'             => ['required', 'string', 'max:255'],
            'product_id'        => ['nullable', 'exists:products,id'],
            'image_url'         => ['nullable', 'url', 'max:2048'],
            'start_price'       => ['required', 'numeric', 'min:0'],
            'reserve_price'     => ['nullable', 'numeric', 'min:0'],
            'min_bid_increment' => ['required', 'numeric', 'min:1'],
            'start_at'          => ['required', 'date'],
            'end_at'            => ['required', 'date', 'after:start_at'],
            'description'       => ['nullable', 'string'],
        ]);

        $auction->update($validated);

        return redirect()->route('admin.auctions.index')
            ->with('status', 'Auction updated successfully.');
    }

    public function end(Auction $auction): RedirectResponse
    {
        if (in_array($auction->status, ['ended', 'cancelled'])) {
            return redirect()->route('admin.auctions.index')
                ->with('error', 'Auction is already ' . $auction->status . '.');
        }

        $topBid = $auction->bids()->orderByDesc('amount')->first();
        $auction->update([
            'status'         => 'ended',
            'winner_user_id' => $topBid?->user_id,
            'winning_bid'    => $topBid?->amount,
        ]);

        if ($topBid) {
            $auction->bids()->update(['is_winning' => false]);
            $topBid->update(['is_winning' => true]);
        }

        return redirect()->route('admin.auctions.index')
            ->with('status', 'Auction ended successfully.');
    }

    public function cancel(Auction $auction): RedirectResponse
    {
        if (in_array($auction->status, ['ended', 'cancelled'])) {
            return redirect()->route('admin.auctions.index')
                ->with('error', 'Auction is already ' . $auction->status . '.');
        }

        $auction->update(['status' => 'cancelled']);

        return redirect()->route('admin.auctions.index')
            ->with('status', 'Auction cancelled.');
    }

    public function bids(Auction $auction): View
    {
        $bids = $auction->bids()
            ->with('user')
            ->orderByDesc('amount')
            ->get();

        return view('admin.auctions.bids', compact('auction', 'bids'));
    }
}
