<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('cleaning_packages')->updateOrInsert(
            ['name' => 'Individuell'],
            [
                'description' => 'Individuell zusammengestellte Reinigung aus einzelnen Leistungen',
                'price' => 0,
                'duration_minutes' => 0,
                'is_available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        DB::table('cleaning_packages')
            ->where('name', 'Individuell')
            ->delete();
    }
};
