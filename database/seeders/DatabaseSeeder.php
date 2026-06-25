<?php

namespace Database\Seeders;

use App\Models\CleaningExtra;
use App\Models\CleaningPackage;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

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
            [
                'name' => 'Individuell',
                'description' => 'Individuell zusammengestellte Reinigung aus einzelnen Leistungen',
                'price' => 0,
                'duration_minutes' => 0,
            ],
        ];

        foreach ($packages as $package) {
            CleaningPackage::create($package + ['is_available' => true]);
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
            CleaningExtra::create([
                'name' => $extra['name'],
                'description' => null,
                'price' => $extra['price'],
                'is_available' => $extra['is_available'],
            ]);
        }
    }
}
