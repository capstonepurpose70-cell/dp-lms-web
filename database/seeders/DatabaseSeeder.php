<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * `php artisan db:seed` — ligtas patakbuhin ulit (pati sa production).
     *
     * Dati gumagawa ito ng "Test User" (test@example.com / password) — default na
     * login na hindi dapat umiiral sa live na system.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,     // skipped kapag may admin na
            SubjectSeeder::class,   // keyed by name + grade
        ]);

        // SectionSeeder gumagawa ng SHS placeholders na nire-rename ng AdvisorySectionSeeder;
        // kapag pinatakbo ulit, babalik ang placeholders. Kaya sa bagong database lang.
        if (Section::count() === 0) {
            $this->call([SectionSeeder::class, AdvisorySectionSeeder::class]);
        } else {
            $this->command?->info('Sections already exist — skipped. Run AdvisorySectionSeeder by itself if advisers still need linking.');
        }
    }
}
