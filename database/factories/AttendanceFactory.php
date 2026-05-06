<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Employer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['present', 'absent', 'late', 'on_leave']);

        // ══════════════════════════════════════════════
        // DATE SALE — format aléatoirement incorrect
        // ══════════════════════════════════════════════
        $dateBase = $this->faker->dateTimeBetween('-6 months', 'now');
        $dateSale = $this->faker->randomElement([
            $dateBase->format('Y-m-d'),           // ✅ correct
            $dateBase->format('d/m/Y'),           // ❌ format européen
            $dateBase->format('d-m-Y'),           // ❌ format inversé
            $dateBase->format('m/d/Y'),           // ❌ format américain
            $dateBase->format('Y/m/d'),           // ❌ slash au lieu de tiret
            $dateBase->format('d.m.Y'),           // ❌ format avec points
        ]);

        $checkInMorning    = null;
        $checkOutMorning   = null;
        $checkInAfternoon  = null;
        $checkOutAfternoon = null;

        if (in_array($status, ['present', 'late'])) {
            $baseDate = Carbon::parse($dateBase->format('Y-m-d'));

            // ══════════════════════════════════════════════
            // HEURES SALES — parfois inversées ou nulles
            // ══════════════════════════════════════════════
            $dirty = $this->faker->boolean(30); // 30% de chance d'avoir des heures sales

            if ($dirty) {
                // Check-in après check-out (incohérent)
                $checkInMorning    = $baseDate->copy()->setTime(12, rand(0, 59));
                $checkOutMorning   = $baseDate->copy()->setTime(8, rand(0, 59));
                $checkInAfternoon  = $baseDate->copy()->setTime(17, rand(0, 59));
                $checkOutAfternoon = $baseDate->copy()->setTime(13, rand(0, 59));
            } else {
                $checkInMorning    = $baseDate->copy()->setTime(
                    $status === 'late' ? $this->faker->numberBetween(9, 10) : 8,
                    $this->faker->numberBetween(0, 59)
                );
                $checkOutMorning   = $baseDate->copy()->setTime(12, $this->faker->numberBetween(0, 30));
                $checkInAfternoon  = $baseDate->copy()->setTime(13, $this->faker->numberBetween(0, 30));
                $checkOutAfternoon = $baseDate->copy()->setTime(17, $this->faker->numberBetween(0, 59));
            }

            // 20% de chance d'avoir des pointages manquants
            if ($this->faker->boolean(20)) {
                $checkOutMorning   = null;
                $checkOutAfternoon = null;
            }
        }

        // ══════════════════════════════════════════════
        // STATUS SALE — parfois incohérent avec les heures
        // ══════════════════════════════════════════════
        // Ex: status=absent mais avec des heures de pointage
        $statusSale = $this->faker->boolean(15)
            ? $this->faker->randomElement(['absent', 'on_leave'])
            : $status;

        return [
            'employer_id'              => Employer::inRandomOrder()->first()?->id ?? 1,
            'date'                     => $dateSale,
            'check_in_morning_time'    => $checkInMorning,
            'check_out_morning_time'   => $checkOutMorning,
            'check_in_afternoon_time'  => $checkInAfternoon,
            'check_out_afternoon_time' => $checkOutAfternoon,
            'status'                   => $statusSale,
        ];
    }
}