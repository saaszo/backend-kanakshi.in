<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Models\BlogPost;
use Illuminate\Support\Facades\Schedule;

Artisan::command('blog:publish-scheduled', function () {
    $now = now();
    $posts = BlogPost::where('status', 'scheduled')
        ->where('published_at', '<=', $now)
        ->get();

    if ($posts->isEmpty()) {
        $this->comment("No scheduled posts to publish at this time.");
        return;
    }

    foreach ($posts as $post) {
        $post->update([
            'status' => 'published',
            'last_updated_at' => $now,
        ]);
        $this->info("Published scheduled post: [{$post->id}] {$post->title}");
    }
})->purpose('Publish scheduled blog posts whose publish time has arrived');

Schedule::command('blog:publish-scheduled')->everyMinute();

