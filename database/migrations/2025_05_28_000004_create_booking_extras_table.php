<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_extras', function (Blueprint $table) {
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('cleaning_extra_id')->constrained()->onDelete('restrict');
            $table->primary(['booking_id', 'cleaning_extra_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_extras');
    }
};
