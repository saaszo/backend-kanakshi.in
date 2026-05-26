<?php

namespace App\Http\Controllers\Api;

use App\Models\Auction;
use App\Models\AuctionBid;
use App\Models\CustomerAccessToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuctionController
{
    public function index(Request $request): JsonResponse
    {
        $filter = $request->query('filter', 'live');

        $query = Auction::with('product');

        if ($filter === 'upcoming') {
            $query->upcoming();
        } elseif ($filter === 'ended') {
            $query->ended();
        } elseif ($filter === 'all') {
            $query->whereIn('status', ['live', 'draft', 'ended']);
        } else {
            $query->live();
        }

        $auctions = $query->orderByDesc('end_at')->get();

        // Sync statuses
        foreach ($auctions as $auction) {
            $auction->syncStatus();
        }

        return response()->json([
            'success' => true,
            'data' => $auctions->map(fn($a) => $this->formatAuction($a)),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $auction = Auction::with('product', 'winner')->findOrFail($id);
        $auction->syncStatus();

        return response()->json([
            'success' => true,
            'data' => $this->formatAuction($auction, true),
        ]);
    }

    public function bids(int $id): JsonResponse
    {
        $auction = Auction::findOrFail($id);

        $bids = $auction->bids()
            ->with('user')
            ->orderByDesc('amount')
            ->limit(50)
            ->get()
            ->map(function ($bid, $index) {
                $name = $bid->user?->name ?? 'Anonymous';
                $masked = strlen($name) > 4
                    ? substr($name, 0, 4) . str_repeat('*', max(2, strlen($name) - 4))
                    : $name . '***';

                return [
                    'rank'        => $index + 1,
                    'bidder'      => $masked,
                    'amount'      => (float) $bid->amount,
                    'is_winning'  => (bool) $bid->is_winning,
                    'placed_at'   => $bid->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $bids,
            'meta' => [
                'total_bids' => (int) $auction->total_bids,
                'total_participants' => (int) $auction->total_participants,
            ],
        ]);
    }

    public function placeBid(Request $request, int $id): JsonResponse
    {
        // Resolve user from Bearer token (sha256 hash lookup, same as CustomerAuthController)
        $bearer = $request->bearerToken();

        if (! $bearer) {
            return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        $tokenModel = CustomerAccessToken::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $bearer))
            ->where(function ($q): void {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $tokenModel || ! $tokenModel->user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized or expired session.'], 401);
        }

        $user = $tokenModel->user;

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $result = DB::transaction(function () use ($id, $request, $user, $validated): array {
            $auction = Auction::query()->lockForUpdate()->findOrFail($id);
            $auction->syncStatus();
            $auction->refresh();

            if (! $auction->isActive()) {
                return [
                    'success' => false,
                    'message' => 'This auction is not currently active.',
                    'status' => 422,
                ];
            }

            $minimumBid = $auction->minimumNextBid();

            if ((float) $validated['amount'] < $minimumBid) {
                return [
                    'success' => false,
                    'message' => "Your bid must be at least ₹" . number_format($minimumBid, 2) . ".",
                    'minimum_bid' => $minimumBid,
                    'status' => 422,
                ];
            }

            $currentWinningBid = $auction->bids()
                ->where('is_winning', true)
                ->orderByDesc('amount')
                ->orderByDesc('id')
                ->first();

            if ($currentWinningBid && $currentWinningBid->user_id === $user->id) {
                return [
                    'success' => false,
                    'message' => 'You are already the highest bidder. Wait for someone else to bid before bidding again.',
                    'status' => 422,
                ];
            }

            $bid = AuctionBid::query()->create([
                'auction_id' => $auction->id,
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'ip_address' => $request->ip(),
                'is_winning' => true,
            ]);

            if ($currentWinningBid) {
                $currentWinningBid->update(['is_winning' => false]);
            }

            $participantCount = $auction->bids()->distinct('user_id')->count('user_id');
            $auction->update([
                'total_bids' => $auction->bids()->count(),
                'total_participants' => $participantCount,
            ]);

            $auction->refresh();

            return [
                'success' => true,
                'message' => 'Your bid has been placed successfully!',
                'data' => [
                    'bid_id' => $bid->id,
                    'amount' => (float) $bid->amount,
                    'current_bid' => $auction->currentHighestBid(),
                    'minimum_next_bid' => $auction->minimumNextBid(),
                    'total_bids' => $auction->total_bids,
                    'seconds_left' => $auction->secondsLeft(),
                ],
                'status' => 200,
            ];
        });

        $status = (int) ($result['status'] ?? 200);
        unset($result['status']);

        return response()->json($result, $status);
    }

    private function formatAuction(Auction $auction, bool $detailed = false): array
    {
        $data = [
            'id'                => $auction->id,
            'title'             => $auction->title,
            'status'            => $auction->status,
            'image_url'         => $auction->image_url,
            'start_price'       => (float) $auction->start_price,
            'current_bid'       => $auction->currentHighestBid(),
            'minimum_next_bid'  => $auction->minimumNextBid(),
            'min_bid_increment' => (float) $auction->min_bid_increment,
            'start_at'          => $auction->start_at?->toIso8601String(),
            'end_at'            => $auction->end_at?->toIso8601String(),
            'seconds_left'      => $auction->secondsLeft(),
            'total_bids'        => (int) $auction->total_bids,
            'total_participants'=> (int) $auction->total_participants,
            'product'           => $auction->product ? [
                'id'    => $auction->product->id,
                'name'  => $auction->product->name,
                'slug'  => $auction->product->slug ?? null,
                'price' => $auction->product->price ?? null,
            ] : null,
        ];

        if ($detailed) {
            $data['description'] = $auction->description;

            if ($auction->status === 'ended') {
                $data['winner'] = $auction->winner ? [
                    'name' => $auction->winner->name,
                    'bid' => $auction->winning_bid ? (float) $auction->winning_bid : null,
                ] : null;
                $data['winning_bid'] = $auction->winning_bid ? (float) $auction->winning_bid : null;
            }
        }

        return $data;
    }
}
