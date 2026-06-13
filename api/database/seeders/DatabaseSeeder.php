<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if ($this->shouldSkipProductionSeeding()) {
            $this->command?->warn('Production seeding skipped. Set ALLOW_PRODUCTION_SEEDING=true only for an intentional data bootstrap.');

            return;
        }

        $seeders = [
            StoreSettingsSeeder::class,
            CatalogDemoSeeder::class,
            AdminPanelFoundationSeeder::class,
        ];

        if (app()->environment(['local', 'testing']) || env('SEED_BLOG_DEMO', false)) {
            $seeders[] = BlogDemoSeeder::class;
        }

        $this->call($seeders);
    }

    private function shouldSkipProductionSeeding(): bool
    {
        return app()->environment('production')
            && ! filter_var((string) env('ALLOW_PRODUCTION_SEEDING', false), FILTER_VALIDATE_BOOL);
    }
}
