<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $horaires = [
            [
                'name'        => 'Horaire Normal',
                'start_time'  => '08:00:00',
                'end_time'    => '17:00:00',
                'break_start' => '12:00:00',
                'break_end'   => '13:00:00',
            ],
            [
                'name'        => 'Horaire Matin',
                'start_time'  => '06:00:00',
                'end_time'    => '14:00:00',
                'break_start' => '10:00:00',
                'break_end'   => '10:30:00',
            ],
            [
                'name'        => 'Horaire Après-midi',
                'start_time'  => '14:00:00',
                'end_time'    => '22:00:00',
                'break_start' => '18:00:00',
                'break_end'   => '18:30:00',
            ],
            [
                'name'        => 'Horaire Nuit',
                'start_time'  => '22:00:00',
                'end_time'    => '06:00:00',
                'break_start' => '02:00:00',
                'break_end'   => '02:30:00',
            ],
        ];

        foreach ($horaires as $horaire) {
            Schedule::firstOrCreate(
                ['name' => $horaire['name']],
                $horaire
            );
        }

        $this->command->info('✓ Horaires créés (Normal, Matin, Après-midi, Nuit).');
    }
}