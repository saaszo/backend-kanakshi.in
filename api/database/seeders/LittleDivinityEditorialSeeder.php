<?php

namespace Database\Seeders;

use App\Services\LittleDivinityEditorialBlogPublisher;
use Illuminate\Database\Seeder;

class LittleDivinityEditorialSeeder extends Seeder
{
    public function run(): void
    {
        /** @var LittleDivinityEditorialBlogPublisher $publisher */
        $publisher = app(LittleDivinityEditorialBlogPublisher::class);
        $publisher->publish(true);
    }
}
