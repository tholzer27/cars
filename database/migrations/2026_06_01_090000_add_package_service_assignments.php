<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cleaning_extra_cleaning_package', function (Blueprint $table) {
            $table->foreignId('cleaning_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cleaning_extra_id')->constrained()->cascadeOnDelete();
            $table->primary(['cleaning_package_id', 'cleaning_extra_id']);
        });

        $services = [
            ['name' => 'Grundreinigung aussen inkl. Aussenscheiben', 'price' => 40, 'is_available' => true],
            ['name' => 'Aussaugen', 'price' => 40, 'is_available' => true],
            ['name' => 'Kofferraum aussaugen', 'price' => 10, 'is_available' => true],
            ['name' => 'Cockpit abwischen', 'price' => 20, 'is_available' => true],
            ['name' => 'Felgen', 'price' => 25, 'is_available' => true],
            ['name' => 'Scheiben innen', 'price' => 20, 'is_available' => true],
            ['name' => 'Fussmattenaufbereitung', 'price' => 25, 'is_available' => true],
            ['name' => 'Cockpit aufbereiten', 'price' => 45, 'is_available' => true],
            ['name' => 'Hand-Aussenwäsche', 'price' => 70, 'is_available' => true],
            ['name' => 'Sitzreinigung', 'price' => 60, 'is_available' => true],
            ['name' => 'Armaturen inkl. Lenkrad', 'price' => 45, 'is_available' => true],
            ['name' => 'Versiegelung von Kunststoffteilen', 'price' => 20, 'is_available' => true],
            ['name' => 'Versiegelung von Kunst-/Lederteilen', 'price' => 50, 'is_available' => true],
            ['name' => 'Dichtungen', 'price' => 20, 'is_available' => true],
            ['name' => 'Türverkleidung und Einstiege', 'price' => 30, 'is_available' => true],
        ];

        foreach ($services as $service) {
            DB::table('cleaning_extras')->updateOrInsert(
                ['name' => $service['name']],
                $service + [
                    'description' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }

        DB::table('cleaning_extras')
            ->where('name', 'Grundreinigung mit Aussenscheiben')
            ->update(['is_available' => false, 'updated_at' => now()]);

        $assignments = [
            'Basis-Paket' => [
                'Grundreinigung aussen inkl. Aussenscheiben',
                'Aussaugen',
                'Kofferraum aussaugen',
                'Cockpit abwischen',
            ],
            'Basis +' => [
                'Grundreinigung aussen inkl. Aussenscheiben',
                'Aussaugen',
                'Kofferraum aussaugen',
                'Cockpit abwischen',
                'Felgen',
                'Scheiben innen',
                'Fussmattenaufbereitung',
            ],
            'Comfort' => [
                'Grundreinigung aussen inkl. Aussenscheiben',
                'Aussaugen',
                'Kofferraum aussaugen',
                'Cockpit aufbereiten',
                'Felgen',
                'Scheiben innen',
                'Fussmattenaufbereitung',
                'Hand-Aussenwäsche',
                'Sitzreinigung',
                'Armaturen inkl. Lenkrad',
                'Versiegelung von Kunststoffteilen',
            ],
            'Premium' => [
                'Grundreinigung aussen inkl. Aussenscheiben',
                'Aussaugen',
                'Kofferraum aussaugen',
                'Cockpit aufbereiten',
                'Felgen',
                'Scheiben innen',
                'Hand-Aussenwäsche',
                'Fussmattenaufbereitung',
                'Sitzreinigung',
                'Armaturen inkl. Lenkrad',
                'Versiegelung von Kunststoffteilen',
                'Versiegelung von Kunst-/Lederteilen',
                'Dichtungen',
                'Türverkleidung und Einstiege',
            ],
        ];

        foreach ($assignments as $packageName => $serviceNames) {
            $packageId = DB::table('cleaning_packages')->where('name', $packageName)->value('id');

            if (! $packageId) {
                continue;
            }

            $serviceIds = DB::table('cleaning_extras')->whereIn('name', $serviceNames)->pluck('id');

            foreach ($serviceIds as $serviceId) {
                DB::table('cleaning_extra_cleaning_package')->insertOrIgnore([
                    'cleaning_package_id' => $packageId,
                    'cleaning_extra_id' => $serviceId,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cleaning_extra_cleaning_package');
    }
};
