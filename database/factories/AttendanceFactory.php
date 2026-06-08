<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $user = User::role('employer')->inRandomOrder()->first();
        $date = Carbon::instance($this->faker->dateTimeBetween('-6 months', 'now'));

        $isLate = $this->faker->boolean(20);
        $heure  = $isLate ? rand(9, 10) : 8;
        $minute = rand(0, 59);

        // Entrée
        return [
            'user_id'           => $user?->id ?? 1,
            'type'              => 'entree',
            'pointage_at'       => $date->copy()->setTime($heure, $minute)->toDateTimeString(),
            'shift_user_id'     => null,
            'face_matched'      => false,
            'tx_hash'           => null,
            'block_number'      => null,
            'blockchain_statut' => 'pending',
            'device_ref'        => null,
        ];
    }

    public function sortie(): static
    {
        return $this->state(function (array $attributes) {
            $pointageAt = Carbon::parse($attributes['pointage_at']);
            return [
                'type'        => 'sortie',
                'pointage_at' => $pointageAt->copy()->setTime(rand(17, 18), rand(0, 59))->toDateTimeString(),
            ];
        });
    }
}