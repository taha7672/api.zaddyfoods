<?php

namespace Database\Seeders;

use App\Models\Translation;
use App\Traits\Loggable;
use DB;
use Illuminate\Database\Seeder;
use Throwable;

class TranslationSeeder extends Seeder
{
    use Loggable;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        try {
            $filePath = resource_path('lang/translations_en.sql');

            if (file_exists($filePath)) {
                Translation::truncate();
                DB::unprepared(file_get_contents($filePath));
            }
        } catch (Throwable $e) {
            $this->error($e);
        }
    }
}
