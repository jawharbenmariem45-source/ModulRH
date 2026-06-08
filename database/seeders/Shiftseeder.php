<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shift;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            [
                'name'       => 'Matin',
                'type'       => 'morning',
                'starts_at'  => '08:00:00',
                'ends_at'    => '12:00:00',
                'pause_start'=> null,
                'pause_end'  => null,
                'work_days'  => json_encode(['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi']),
                'is_default' => true,
                'actif'      => true,
            ],
            [
                'name'       => 'Après-midi',
                'type'       => 'afternoon',
                'starts_at'  => '13:00:00',
                'ends_at'    => '17:00:00',
                'pause_start'=> null,
                'pause_end'  => null,
                'work_days'  => json_encode(['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi']),
                'is_default' => false,
                'actif'      => true,
            ],
            [
                'name'       => 'Journée complète',
                'type'       => 'two_shifts',
                'starts_at'  => '08:00:00',
                'ends_at'    => '17:00:00',
                'pause_start'=> '12:00:00',
                'pause_end'  => '13:00:00',
                'work_days'  => json_encode(['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi']),
                'is_default' => false,
                'actif'      => true,
            ],
            [
                'name'       => 'Nuit',
                'type'       => 'night',
                'starts_at'  => '21:00:00',
                'ends_at'    => '05:00:00',
                'pause_start'=> '01:00:00',
                'pause_end'  => '01:30:00',
                'work_days'  => json_encode(['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi']),
                'is_default' => false,
                'actif'      => true,
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::firstOrCreate(
                ['name' => $shift['name']],
                $shift
            );
        }

        $this->command->info('✓ Shifts créés.');
    }
}