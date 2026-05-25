<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Auction extends Model {
    protected $fillable = ['product_id','title','description','image_url','start_price','reserve_price','min_bid_increment','start_at','end_at','status','winner_user_id','winning_bid','total_bids','total_participants','created_by'];
    protected $casts = ['start_at'=>'datetime','end_at'=>'datetime','start_price'=>'decimal:2','reserve_price'=>'decimal:2','min_bid_increment'=>'decimal:2','winning_bid'=>'decimal:2'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function bids(): HasMany { return $this->hasMany(AuctionBid::class); }
    public function winner(): BelongsTo { return $this->belongsTo(User::class, 'winner_user_id'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function currentHighestBid(): float {
        $highest = $this->bids()->max('amount');
        return (float)($highest ?? $this->start_price);
    }

    public function minimumNextBid(): float {
        return $this->currentHighestBid() + (float)$this->min_bid_increment;
    }

    public function isActive(): bool {
        return $this->status === 'live' && now()->between($this->start_at, $this->end_at);
    }

    public function secondsLeft(): int {
        if (!$this->isActive()) return 0;
        return max(0, (int)now()->diffInSeconds($this->end_at, false));
    }

    public function syncStatus(): void {
        if ($this->status === 'cancelled') return;
        if (now()->lt($this->start_at)) {
            if ($this->status !== 'draft') $this->update(['status'=>'draft']);
        } elseif (now()->between($this->start_at, $this->end_at)) {
            if ($this->status !== 'live') $this->update(['status'=>'live']);
        } elseif (now()->gt($this->end_at)) {
            if ($this->status !== 'ended') {
                $topBid = $this->bids()->orderByDesc('amount')->first();
                $this->update(['status'=>'ended','winner_user_id'=>$topBid?->user_id,'winning_bid'=>$topBid?->amount]);
                if ($topBid) { $this->bids()->update(['is_winning'=>false]); $topBid->update(['is_winning'=>true]); }
            }
        }
    }

    public function scopeLive($query) { return $query->where('status','live')->where('start_at','<=',now())->where('end_at','>',now()); }
    public function scopeUpcoming($query) { return $query->where('status','draft')->where('start_at','>',now()); }
    public function scopeEnded($query) { return $query->where('status','ended'); }
}
