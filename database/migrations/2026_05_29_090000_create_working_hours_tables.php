<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('working_hours', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('weekday')->unique();
            $table->boolean('is_working')->default(false);
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('working_day_exceptions', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->boolean('is_working')->default(false);
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('working_hours')->insert([
            ['weekday' => 1, 'is_working' => true, 'starts_at' => '08:00', 'ends_at' => '18:00', 'created_at' => $now, 'updated_at' => $now],
            ['weekday' => 2, 'is_working' => true, 'starts_at' => '08:00', 'ends_at' => '18:00', 'created_at' => $now, 'updated_at' => $now],
            ['weekday' => 3, 'is_working' => true, 'starts_at' => '08:00', 'ends_at' => '18:00', 'created_at' => $now, 'updated_at' => $now],
            ['weekday' => 4, 'is_working' => true, 'starts_at' => '08:00', 'ends_at' => '18:00', 'created_at' => $now, 'updated_at' => $now],
            ['weekday' => 5, 'is_working' => true, 'starts_at' => '08:00', 'ends_at' => '18:00', 'created_at' => $now, 'updated_at' => $now],
            ['weekday' => 6, 'is_working' => false, 'starts_at' => null, 'ends_at' => null, 'created_at' => $now, 'updated_at' => $now],
            ['weekday' => 7, 'is_working' => false, 'starts_at' => null, 'ends_at' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('working_day_exceptions');
        Schema::dropIfExists('working_hours');
    }
};
