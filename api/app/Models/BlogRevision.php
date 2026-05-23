<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogRevision extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'blog_post_id',
        'title',
        'excerpt',
        'content',
        'faq_json',
        'updated_by',
        'created_at',
    ];

    protected $casts = [
        'faq_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
