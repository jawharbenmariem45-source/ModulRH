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
        $status   = $this->faker->randomElement(['present', 'absent', 'late', 'on_leave']);
        $dateBase = $this->faker->dateTimeBetween('-6 months', 'now');
        $dateSale = $this->faker->randomElement([
            $dateBase->format('Y-m-d'),
            $dateBase->format('d/m/Y'),
            $dateBase->format('d-m-Y'),
            $dateBase->format('m/d/Y'),
            $dateBase->format('Y/m/d'),
            $dateBase->format('d.m.Y'),
        ]);

        $morningCheckIn    = null;
        $morningCheckOut   = null;
        $afternoonCheckIn  = null;
        $afternoonCheckOut = null;

        if (in_array($status, ['present', 'late'])) {
            $baseDate = Carbon::parse($dateBase->format('Y-m-d'));
            $dirty    = $this->faker->boolean(30);

            if ($dirty) {
                $morningCheckIn    = $baseDate->copy()->setTime(12, rand(0, 59));
                $morningCheckOut   = $baseDate->copy()->setTime(8, rand(0, 59));
                $afternoonCheckIn  = $baseDate->copy()->setTime(17, rand(0, 59));
                $afternoonCheckOut = $baseDate->copy()->setTime(13, rand(0, 59));
            } else {
                $morningCheckIn    = $baseDate->copy()->setTime(
                    $status === 'late' ? $this->faker->numberBetween(9, 10) : 8,
                    $this->faker->numberBetween(0, 59)
                );
                $morningCheckOut   = $baseDate->copy()->setTime(12, $this->faker->numberBetween(0, 30));
                $afternoonCheckIn  = $baseDate->copy()->setTime(13, $this->faker->numberBetween(0, 30));
                $afternoonCheckOut = $baseDate->copy()->setTime(17, $this->faker->numberBetween(0, 59));
            }

            if ($this->faker->boolean(20)) {
                $morningCheckOut   = null;
                $afternoonCheckOut = null;
            }
        }

        $statusSale = $this->faker->boolean(15)
            ? $this->faker->randomElement(['absent', 'on_leave'])
            : $status;

        return [
            'employer_id'         => Employer::inRandomOrder()->first()?->id ?? 1,
            'date'                => $dateSale,
            'morning_check_in'    => $morningCheckIn,
            'morning_check_out'   => $morningCheckOut,
            'afternoon_check_in'  => $afternoonCheckIn,
            'afternoon_check_out' => $afternoonCheckOut,
            'status'              => $statusSale,
        ];
    }
}