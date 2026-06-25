<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('working_day_exceptions', function (Blueprint $table) {
            $table->date('starts_on')->nullable()->after('id');
            $table->date('ends_on')->nullable()->after('starts_on');
        });

        DB::table('working_day_exceptions')->update([
            'starts_on' => DB::raw('date'),
            'ends_on' => DB::raw('date'),
        ]);
    }

    public function down(): void
    {
        Schema::table('working_day_exceptions', function (Blueprint $table) {
            $table->dropColumn(['starts_on', 'ends_on']);
        });
    }
};
