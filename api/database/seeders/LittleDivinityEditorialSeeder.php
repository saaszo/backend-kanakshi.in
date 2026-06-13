<?php

namespace Database\Seeders;

use App\Services\LittleDivinityEditorialBlogPublisher;
use Illuminate\Database\Seeder;

class LittleDivinityEditorialSeeder extends Seeder
{
    public function run(): void
    {
        if ($this->shouldSkipProductionSeeding()) {
            $this->command?->warn('Editorial seeding skipped in production.');

            return;
        }

        /** @var LittleDivinityEditorialBlogPublisher $publisher */
        $publisher = app(LittleDivinityEditorialBlogPublisher::class);
        $publisher->publish(true);
    }

    private function shouldSkipProductionSeeding(): bool
    {
        return app()->environment('production')
            && ! filter_var((string) env('ALLOW_PRODUCTION_SEEDING', false), FILTER_VALIDATE_BOOL);
    }
}
