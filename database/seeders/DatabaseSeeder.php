<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * `php artisan db:seed` — safe to run again (production included).
     * It creates no default login (no test@example.com / password).
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,     // skipped when an admin exists
            SubjectSeeder::class,   // keyed by name + grade
        ]);

        // SectionSeeder creates SHS placeholders that AdvisorySectionSeeder renames;
        // running it again would bring the placeholders back. So: fresh databases only.
        if (Section::count() === 0) {
            $this->call([SectionSeeder::class, AdvisorySectionSeeder::class]);
        } else {
            $this->command?->info('Sections already exist — skipped. Run AdvisorySectionSeeder by itself if advisers still need linking.');
        }
    }
}
