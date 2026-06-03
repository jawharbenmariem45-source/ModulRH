<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    const CNSS_TAUX      = 0.0968;
    const CNSS_PLAFOND   = 6000;
    const FRAIS_PRO_TAUX = 0.20;
    const FRAIS_PRO_MAX  = 2000;
    const CSS_TAUX       = 0.005;
    const CSS_SEUIL      = 5000;
    const KARAMA_SUBV    = 400.0;
    const CIVP_ANETI     = 200.0;

    private function calculerIRPPAnnuel(float $aai): float
    {
        if ($aai <= 0)      return 0;
        if ($aai <= 5000)   return 0;
        if ($aai <= 10000)  return ($aai - 5000)  * 0.15;
        if ($aai <= 20000)  return 750  + ($aai - 10000) * 0.25;
        if ($aai <= 30000)  return 3250 + ($aai - 20000) * 0.30;
        if ($aai <= 40000)  return 6250 + ($aai - 30000) * 0.33;
        if ($aai <= 50000)  return 9550 + ($aai - 40000) * 0.36;
        if ($aai <= 70000)  return 13150 + ($aai - 50000) * 0.38;
        return 20750 + ($aai - 70000) * 0.40;
    }

    private function calculerPaie(User $user): array
    {
        $contractType = $user->contract_type ?? 'CDI';
        $baseSalary   = is_numeric($user->salary) ? (float) $user->salary : 800;

        $overtimeHours  = 0;
        $overtimeAmount = 0;
        $bonuses        = 0;
        $allowances     = 0;
        $cnss           = 0;
        $irpp           = 0;
        $css            = 0;
        $grossSalary    = 0;
        $amount         = 0;

        switch ($contractType) {
            case 'CDI':
            case 'CDD':
                $overtimeHours  = $this->faker->numberBetween(0, 20);
                $bonuses        = $this->faker->numberBetween(0, 100);
                $allowances     = $this->faker->numberBetween(0, 50);
                $tauxHoraire    = $baseSalary / 176;
                $overtimeAmount = 0;
                if ($overtimeHours > 0) {
                    $heuresAvant48 = 32;
                    if ($overtimeHours <= $heuresAvant48) {
                        $overtimeAmount = round($overtimeHours * $tauxHoraire * 1.25, 3);
                    } else {
                        $overtimeAmount = round(
                            ($heuresAvant48 * $tauxHoraire * 1.25) +
                            (($overtimeHours - $heuresAvant48) * $tauxHoraire * 1.50),
                            3
                        );
                    }
                }
                $grossSalary = $baseSalary + $overtimeAmount + $bonuses + $allowances;
                $cnss        = round(min($grossSalary, self::CNSS_PLAFOND) * self::CNSS_TAUX, 3);
                $sncMensuel  = $grossSalary - $cnss;
                $fraisPro    = min($sncMensuel * 12 * self::FRAIS_PRO_TAUX, self::FRAIS_PRO_MAX);
                $aai         = max($sncMensuel * 12 - $fraisPro, 0);
                $irpp        = round($this->calculerIRPPAnnuel($aai) / 12, 3);
                $css         = $aai > self::CSS_SEUIL ? round(($aai * self::CSS_TAUX) / 12, 3) : 0;
                $amount      = round($grossSalary - $cnss - $irpp - $css, 3);
                break;

            case 'CIVP':
                $grossSalary = $baseSalary;
                $cnss        = 0;
                $irpp        = 0;
                $css         = 0;
                $amount      = $baseSalary;
                break;

            case 'Karama':
                $grossSalary = max($baseSalary, 200.0);
                $cnss        = 0;
                $fraisPro    = min($grossSalary * 12 * self::FRAIS_PRO_TAUX, self::FRAIS_PRO_MAX);
                $aai         = max($grossSalary * 12 - $fraisPro, 0);
                $irpp        = round($this->calculerIRPPAnnuel($aai) / 12, 3);
                $css         = $aai > self::CSS_SEUIL ? round(($aai * self::CSS_TAUX) / 12, 3) : 0;
                $amount      = round(($grossSalary - $irpp - $css) + self::KARAMA_SUBV, 3);
                break;

            default:
                $grossSalary = $baseSalary;
                $cnss        = round(min($grossSalary, self::CNSS_PLAFOND) * self::CNSS_TAUX, 3);
                $sncMensuel  = $grossSalary - $cnss;
                $fraisPro    = min($sncMensuel * 12 * self::FRAIS_PRO_TAUX, self::FRAIS_PRO_MAX);
                $aai         = max($sncMensuel * 12 - $fraisPro, 0);
                $irpp        = round($this->calculerIRPPAnnuel($aai) / 12, 3);
                $css         = $aai > self::CSS_SEUIL ? round(($aai * self::CSS_TAUX) / 12, 3) : 0;
                $amount      = round($grossSalary - $cnss - $irpp - $css, 3);
        }

        return compact(
            'contractType', 'baseSalary',
            'overtimeHours', 'overtimeAmount', 'bonuses', 'allowances',
            'grossSalary', 'cnss', 'irpp', 'css', 'amount'
        );
    }

    public function definition(): array
    {
        $user    = User::role('employer')->inRandomOrder()->first();
        $moisInt = $this->faker->numberBetween(1, 12);
        $annee   = $this->faker->numberBetween(2024, 2026);

        try {
            $launchDate = Carbon::create($annee, $moisInt, rand(25, 28), rand(8, 17), rand(0, 59));
        } catch (\Exception $e) {
            $launchDate = Carbon::now();
        }
        $doneTime = $launchDate->copy()->addMinutes(rand(1, 60));

        $paie = $this->calculerPaie($user);

        return [
            'reference'       => 'PAY-' . strtoupper($this->faker->bothify('??####')),
            'user_id'         => $user->id,
            'contract_type'   => $paie['contractType'],
            'base_salary'     => $paie['baseSalary'],
            'overtime_hours'  => $paie['overtimeHours'],
            'overtime_amount' => $paie['overtimeAmount'],
            'bonuses'         => $paie['bonuses'],
            'allowances'      => $paie['allowances'],
            'gross_salary'    => round($paie['grossSalary'], 3),
            'cnss'            => $paie['cnss'],
            'irpp'            => $paie['irpp'],
            'css'             => $paie['css'],
            'amount'          => $paie['amount'],
            'launch_date'     => $launchDate->toDateString(),
            'done_time'       => $doneTime->toDateTimeString(),
            'month'           => $moisInt,
            'year'            => $annee,
            'status'          => 'done',
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(function (array $attributes) use ($user) {
            $paie = $this->calculerPaie($user);
            return [
                'user_id'         => $user->id,
                'contract_type'   => $paie['contractType'],
                'base_salary'     => $paie['baseSalary'],
                'overtime_hours'  => $paie['overtimeHours'],
                'overtime_amount' => $paie['overtimeAmount'],
                'bonuses'         => $paie['bonuses'],
                'allowances'      => $paie['allowances'],
                'gross_salary'    => round($paie['grossSalary'], 3),
                'cnss'            => $paie['cnss'],
                'irpp'            => $paie['irpp'],
                'css'             => $paie['css'],
                'amount'          => $paie['amount'],
                'status'          => 'done',
            ];
        });
    }
}