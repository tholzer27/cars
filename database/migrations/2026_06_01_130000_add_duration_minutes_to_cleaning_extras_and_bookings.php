<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cleaning_extras', function (Blueprint $table) {
            $table->unsignedInteger('duration_minutes')->default(30)->after('price');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedInteger('duration_minutes')->default(60)->after('total_price');
        });

        $durations = [
            'Motorreinigung' => 60,
            'Keramikversiegelung' => 180,
            'Politur' => 180,
            'Scheinwerfer-Aufbereitung' => 60,
            'Tierhaarentfernung' => 60,
            'Dichtungen' => 30,
            'Ozonbehandlung' => 60,
            'Fleckenentfernung' => 60,
            'Unterbodenreinigung' => 60,
            'Verdeckreinigung' => 90,
            'Insektenentfernung' => 30,
            'Grundreinigung mit Aussenscheiben' => 40,
            'Aussaugen' => 40,
            'Kofferraum aussaugen' => 15,
            'Scheiben innen' => 20,
            'Grundreinigung aussen inkl. Aussenscheiben' => 40,
            'Cockpit abwischen' => 20,
            'Felgen' => 20,
            'Fussmattenaufbereitung' => 30,
            'Cockpit aufbereiten' => 40,
            'Hand-Aussenwäsche' => 60,
            'Sitzreinigung' => 60,
            'Armaturen inkl. Lenkrad' => 30,
            'Versiegelung von Kunststoffteilen' => 30,
            'Versiegelung von Kunst-/Lederteilen' => 45,
            'Türverkleidung und Einstiege' => 30,
        ];

        foreach ($durations as $name => $duration) {
            DB::table('cleaning_extras')
                ->where('name', $name)
                ->update(['duration_minutes' => $duration]);
        }
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('duration_minutes');
        });

        Schema::table('cleaning_extras', function (Blueprint $table) {
            $table->dropColumn('duration_minutes');
        });
    }
};
