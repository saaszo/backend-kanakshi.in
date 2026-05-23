<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\LittleDivinityEditorialBlogPublisher;
use App\Services\LittleDivinityBlogImporter;

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

Artisan::command('blog:import-little-divinity {--refresh : Update matching imported slugs if they already exist}', function () {
    /** @var LittleDivinityBlogImporter $importer */
    $importer = app(LittleDivinityBlogImporter::class);
    $result = $importer->import((bool) $this->option('refresh'));

    foreach ($result['urls'] as $url) {
        $this->line("Found source article: {$url}");
    }

    $this->newLine();
    $this->info("Imported: {$result['imported']}");
    $this->info("Updated: {$result['updated']}");
    $this->comment("Skipped: {$result['skipped']}");

    if (!empty($result['errors'])) {
        $this->newLine();
        $this->error('Import finished with some issues:');
        foreach ($result['errors'] as $error) {
            $this->line("- {$error}");
        }
    } else {
        $this->newLine();
        $this->info('Little Divinity blog import completed successfully.');
    }
})->purpose('Import published Little Divinity Shopify blog articles into the local blog CMS');

Artisan::command('blog:seed-little-divinity-editorial {--no-refresh : Skip existing matching slugs instead of updating them}', function () {
    /** @var LittleDivinityEditorialBlogPublisher $publisher */
    $publisher = app(LittleDivinityEditorialBlogPublisher::class);
    $refresh = !$this->option('no-refresh');
    $result = $publisher->publish($refresh);

    foreach ($result['slugs'] as $slug) {
        $this->line("Prepared editorial blog: {$slug}");
    }

    $this->newLine();
    $this->info("Created: {$result['created']}");
    $this->info("Updated: {$result['updated']}");
    $this->comment("Skipped: {$result['skipped']}");
    $this->newLine();
    $this->info('Little Divinity editorial blog publish completed successfully.');
})->purpose('Seed curated Little Divinity editorial blog content into the blog CMS');
