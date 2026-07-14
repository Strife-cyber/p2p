<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Uses ComprehensiveDatabaseSeeder for rich demo data across all entities.
     * Auto-seeded on container boot via docker-entrypoint.sh (`migrate --seed`).
     * Runs only when migrations actually execute, so it's safe for repeated restarts.
     *
     * To skip seeding in production without running new migrations:
     *   php artisan db:seed --class=ComprehensiveDatabaseSeeder
     * To reset and re-seed:
     *   php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        $this->call([
            ComprehensiveDatabaseSeeder::class,
        ]);
    }
}
