<?php

use Illuminate\Database\Seeder;

/**
 * Class MultSeeder
 *
 * @author Andrey Girnik <girnikandrey@gmail.com>
 */
class MultSeeder extends Seeder
{
    /**
     * Insert a new entry in to the languages table.
     * @return void
     */
    public function run()
    {
        DB::statement("INSERT INTO languages(`locale`, `short_name`, `name`, `default`, `created_at`, `updated_at`) VALUES ('en-EN', 'en', 'English', 1, NOW(), NOW())");
    }
}
