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
        $status = $this->faker->randomElement([
            'present', 'present', 'present', 'present',
            'absent', 'late', 'on_leave',
        ]);

        $date = Carbon::instance(
            $this->faker->dateTimeBetween('-6 months', 'now')
        )->toDateString();

        $morningCheckIn    = null;
        $morningCheckOut   = null;
        $afternoonCheckIn  = null;
        $afternoonCheckOut = null;

        if (in_array($status, ['present', 'late'])) {
            $heureEntree = $status === 'late' ? $this->faker->numberBetween(9, 10) : 8;
            $min = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);

            $casIncomplet = $this->faker->randomElement([
                'complet', 'complet', 'complet', 'complet', 'complet', 'complet',
                'no_checkout_matin', 'no_checkin_apmidi',
                'no_checkout_apmidi', 'matin_seulement', 'apmidi_seulement',
            ]);

            switch ($casIncomplet) {
                case 'complet':
                    $morningCheckIn    = $date . ' ' . $heureEntree . ':' . $min . ':00';
                    $morningCheckOut   = $date . ' 12:' . str_pad(rand(0, 30), 2, '0', STR_PAD_LEFT) . ':00';
                    $afternoonCheckIn  = $date . ' 13:' . str_pad(rand(0, 30), 2, '0', STR_PAD_LEFT) . ':00';
                    $afternoonCheckOut = $date . ' 17:' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ':00';
                    break;
                case 'no_checkout_matin':
                    $morningCheckIn    = $date . ' ' . $heureEntree . ':' . $min . ':00';
                    $afternoonCheckIn  = $date . ' 13:' . str_pad(rand(0, 30), 2, '0', STR_PAD_LEFT) . ':00';
                    $afternoonCheckOut = $date . ' 17:' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ':00';
                    break;
                case 'no_checkin_apmidi':
                    $morningCheckIn    = $date . ' ' . $heureEntree . ':' . $min . ':00';
                    $morningCheckOut   = $date . ' 12:' . str_pad(rand(0, 30), 2, '0', STR_PAD_LEFT) . ':00';
                    $afternoonCheckOut = $date . ' 17:' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ':00';
                    break;
                case 'no_checkout_apmidi':
                    $morningCheckIn   = $date . ' ' . $heureEntree . ':' . $min . ':00';
                    $morningCheckOut  = $date . ' 12:' . str_pad(rand(0, 30), 2, '0', STR_PAD_LEFT) . ':00';
                    $afternoonCheckIn = $date . ' 13:' . str_pad(rand(0, 30), 2, '0', STR_PAD_LEFT) . ':00';
                    break;
                case 'matin_seulement':
                    $morningCheckIn  = $date . ' ' . $heureEntree . ':' . $min . ':00';
                    $morningCheckOut = $date . ' 12:' . str_pad(rand(0, 30), 2, '0', STR_PAD_LEFT) . ':00';
                    break;
                case 'apmidi_seulement':
                    $afternoonCheckIn  = $date . ' 13:' . str_pad(rand(0, 30), 2, '0', STR_PAD_LEFT) . ':00';
                    $afternoonCheckOut = $date . ' 17:' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ':00';
                    break;
            }
        }

        return [
            'user_id'             => User::role('employer')->inRandomOrder()->first()?->id ?? 1,
            'date'                => $date,
            'morning_check_in'    => $morningCheckIn,
            'morning_check_out'   => $morningCheckOut,
            'afternoon_check_in'  => $afternoonCheckIn,
            'afternoon_check_out' => $afternoonCheckOut,
            'status'              => $status,
        ];
    }
}