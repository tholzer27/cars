<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('cleaning_packages')->update(['is_available' => false]);
        DB::table('cleaning_extras')->update(['is_available' => false]);

        $packages = [
            [
                'name' => 'Basis-Paket',
                'description' => implode("\n", [
                    'Grundreinigung aussen inkl. Aussenscheiben',
                    'Aussaugen',
                    'Kofferraum aussaugen',
                    'Cockpit abwischen',
                ]),
                'price' => 80,
                'duration_minutes' => 60,
            ],
            [
                'name' => 'Basis +',
                'description' => implode("\n", [
                    'Grundreinigung aussen inkl. Aussenscheiben',
                    'Aussaugen',
                    'Kofferraum aussaugen',
                    'Cockpit abwischen',
                    'Felgen',
                    'Scheiben innen',
                    'Fussmattenaufbereitung',
                ]),
                'price' => 120,
                'duration_minutes' => 90,
            ],
            [
                'name' => 'Comfort',
                'description' => implode("\n", [
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
                ]),
                'price' => 200,
                'duration_minutes' => 150,
            ],
            [
                'name' => 'Premium',
                'description' => implode("\n", [
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
                ]),
                'price' => 250,
                'duration_minutes' => 180,
            ],
        ];

        foreach ($packages as $package) {
            DB::table('cleaning_packages')->updateOrInsert(
                ['name' => $package['name']],
                $package + [
                    'is_available' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }

        $extras = [
            ['name' => 'Motorreinigung', 'price' => 0, 'is_available' => false],
            ['name' => 'Keramikversiegelung', 'price' => 0, 'is_available' => false],
            ['name' => 'Politur', 'price' => 0, 'is_available' => false],
            ['name' => 'Scheinwerfer-Aufbereitung', 'price' => 0, 'is_available' => false],
            ['name' => 'Tierhaarentfernung', 'price' => 40, 'is_available' => true],
            ['name' => 'Dichtungen', 'price' => 20, 'is_available' => true],
            ['name' => 'Ozonbehandlung', 'price' => 0, 'is_available' => false],
            ['name' => 'Fleckenentfernung', 'price' => 0, 'is_available' => false],
            ['name' => 'Unterbodenreinigung', 'price' => 0, 'is_available' => false],
            ['name' => 'Verdeckreinigung', 'price' => 0, 'is_available' => false],
            ['name' => 'Insektenentfernung', 'price' => 10, 'is_available' => true],
            ['name' => 'Grundreinigung mit Aussenscheiben', 'price' => 40, 'is_available' => true],
            ['name' => 'Aussaugen', 'price' => 40, 'is_available' => true],
            ['name' => 'Kofferraum aussaugen', 'price' => 10, 'is_available' => true],
            ['name' => 'Scheiben innen', 'price' => 20, 'is_available' => true],
        ];

        foreach ($extras as $extra) {
            DB::table('cleaning_extras')->updateOrInsert(
                ['name' => $extra['name']],
                $extra + [
                    'description' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    public function down(): void
    {
        //
    }
};
