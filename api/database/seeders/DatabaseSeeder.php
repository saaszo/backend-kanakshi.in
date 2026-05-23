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
}
