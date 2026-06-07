<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Company;
use App\Models\Departement;
use App\Models\Poste;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserFactory extends Factory
{
    protected $model = User::class;

    private static array $prenoms_h = [
        'Mohamed', 'Ahmed', 'Ali', 'Omar', 'Youssef', 'Amine', 'Bilel',
        'Karim', 'Nabil', 'Tarek', 'Sami', 'Hedi', 'Walid', 'Riadh',
        'Farouk', 'Khaled', 'Zied', 'Maher', 'Sofien', 'Hatem', 'Anis',
        'Bassem', 'Chokri', 'Fares', 'Hamza', 'Issam', 'Jawher', 'Lotfi',
    ];

    private static array $prenoms_f = [
        'Fatima', 'Amina', 'Nour', 'Rania', 'Sana', 'Ines', 'Dorra',
        'Hela', 'Leila', 'Olfa', 'Rim', 'Sirine', 'Salma', 'Nesrine',
        'Wafa', 'Hana', 'Mariem', 'Sahar', 'Asma', 'Yasmine', 'Cyrine',
        'Emna', 'Ghada', 'Jihene', 'Kenza', 'Lobna', 'Meriem', 'Nawres',
    ];

    private static array $noms = [
        'Ben Ali', 'Trabelsi', 'Hamdi', 'Mansouri', 'Chaabane', 'Jebali',
        'Karray', 'Zouari', 'Dridi', 'Belhaj', 'Maatoug', 'Gharbi',
        'Bouazizi', 'Bensalem', 'Nasr', 'Hadhri', 'Tlili', 'Gafsi',
        'Mejri', 'Chebbi', 'Saidani', 'Belhadj', 'Kouki', 'Oueslati',
        'Dhahri', 'Elloumi', 'Fakhfakh', 'Guizani', 'Hamouda', 'Jelassi',
    ];

    private static array $operateurs = [
        '20', '21', '22', '23', '24', '25', '26', '27',
        '50', '51', '52', '53', '54', '55',
        '90', '91', '92', '93', '94', '95', '96', '97', '98',
    ];

    private static array $salaires = [
        1 => [1000, 4000],
        2 => [600,  2500],
        3 => [700,  3000],
        4 => [500,  2500],
        5 => [800,  3500],
        6 => [470,  1800],
        7 => [470,  1600],
        8 => [500,  2000],
    ];

    private static array $domaines = [
        1 => 'alphacorp.tn',
        2 => 'technova.tn',
        3 => 'summitrise.tn',
    ];

    private static int $compteur = 1;

    private function genererPrenom(): string
    {
        $tous = array_merge(self::$prenoms_h, self::$prenoms_f);
        return $tous[array_rand($tous)];
    }

    private function genererNom(): string
    {
        return self::$noms[array_rand(self::$noms)];
    }

    private function genererTelephone(): string
    {
        $op    = self::$operateurs[array_rand(self::$operateurs)];
        $reste = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        return '+216 ' . $op . ' ' . substr($reste, 0, 3) . ' ' . substr($reste, 3);
    }

    private function genererCnss(): string
    {
        return str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT) . '-' . rand(0, 9);
    }

    private function genererRib(): string
    {
        $banque = str_pad(rand(10, 99), 2, '0', STR_PAD_LEFT);
        $agence = str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
        $compte = str_pad(rand(0, 9999999999999), 13, '0', STR_PAD_LEFT);
        $cle    = str_pad(rand(10, 99), 2, '0', STR_PAD_LEFT);
        return $banque . $agence . $compte . $cle;
    }

    private function genererSalaire(int $deptId, string $contractType): int
    {
        if ($contractType === 'CIVP')   return $this->faker->numberBetween(200, 400);
        if ($contractType === 'Karama') return $this->faker->numberBetween(200, 800);
        $plage = self::$salaires[$deptId] ?? [470, 2000];
        return $this->faker->numberBetween($plage[0], $plage[1]);
    }

    private function genererDisciplineScore(): int
    {
        $rand = rand(1, 100);
        if ($rand <= 40) return rand(90, 100);
        if ($rand <= 70) return rand(70, 89);
        if ($rand <= 90) return rand(50, 69);
        return rand(20, 49);
    }

    public function definition(): array
    {
        $dept         = Departement::inRandomOrder()->first();
        $deptId       = $dept?->id ?? 1;
        $contractType = $this->faker->randomElement(['CDI', 'CDD', 'CIVP', 'Karama']);

        $poste    = Poste::where('departement_id', $deptId)->inRandomOrder()->first();
        $schedule = Schedule::inRandomOrder()->first();

        $prenom = $this->genererPrenom();
        $nom    = $this->genererNom();

        $prenomAscii = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $prenom));
        $nomAscii    = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', explode(' ', $nom)[0]));
        $compteur    = self::$compteur++;

        // Email temporaire — sera corrigé par afterCreating selon company_id réel
        $email = $prenomAscii . '.' . $nomAscii . '.' . $compteur . '@entreprise.tn';

        $startDate = $this->faker->dateTimeBetween('-3 years', '-1 month');
        $endDate   = in_array($contractType, ['CDD', 'CIVP', 'Karama'])
            ? Carbon::parse($startDate)->addMonths(rand(6, 24))
            : null;

        return [
            'name'                    => $prenom . ' ' . $nom,
            'last_name'               => $nom,
            'first_name'              => $prenom,
            'email'                   => $email,
            'email_verified_at'       => now(),
            'password'                => Hash::make('password'),
            'phone'                   => $this->genererTelephone(),
            'gender'                  => $this->faker->randomElement(['Homme', 'Femme']),
            'departement_id'          => $deptId,
            'company_id'              => 3, // sera overridé par state()
            'poste_id'                => $poste?->id,
            'schedule_id'             => $schedule?->id,
            'salary'                  => $this->genererSalaire($deptId, $contractType),
            'discipline_score'        => $this->genererDisciplineScore(),
            'family_head'             => $this->faker->boolean(30),
            'children_count'          => $this->faker->numberBetween(0, 4),
            'disabled_children_count' => $this->faker->boolean(10) ? 1 : 0,
            'student_children_count'  => $this->faker->numberBetween(0, 2),
            'contract_type'           => $contractType,
            'rib'                     => $this->genererRib(),
            'rib_image'               => null,
            'cnss'                    => $this->genererCnss(),
            'start_date'              => Carbon::parse($startDate)->toDateString(),
            'end_date'                => $endDate?->toDateString(),
            'remember_token'          => Str::random(10),
        ];
    }

    // ── Corriger l'email après création selon company_id réel ─────────────────
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            $domaines    = self::$domaines;
            $domaine     = $domaines[$user->company_id] ?? 'entreprise.tn';
            $prenomAscii = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $user->first_name ?? 'user'));
            $nomAscii    = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', explode(' ', $user->last_name ?? 'nom')[0]));

            $user->update([
                'email' => $prenomAscii . '.' . $nomAscii . '.' . $user->id . '@' . $domaine,
            ]);
        });
    }

    // ── States ────────────────────────────────────────────────────────────────

    public function unverified(): static
    {
        return $this->state(fn() => ['email_verified_at' => null]);
    }

    public function ancien(): static
    {
        return $this->state(function () {
            $dept   = Departement::inRandomOrder()->first();
            $deptId = $dept?->id ?? 1;
            $poste  = Poste::where('departement_id', $deptId)->inRandomOrder()->first();
            return [
                'contract_type'    => 'CDI',
                'departement_id'   => $deptId,
                'poste_id'         => $poste?->id,
                'start_date'       => Carbon::now()->subYears(rand(3, 10))->startOfMonth()->toDateString(),
                'end_date'         => null,
                'discipline_score' => rand(75, 100),
                'salary'           => $this->genererSalaire($deptId, 'CDI'),
            ];
        });
    }

    public function cdi(): static
    {
        return $this->state(function () {
            $dept   = Departement::inRandomOrder()->first();
            $deptId = $dept?->id ?? 1;
            $poste  = Poste::where('departement_id', $deptId)->inRandomOrder()->first();
            return [
                'contract_type'  => 'CDI',
                'departement_id' => $deptId,
                'poste_id'       => $poste?->id,
                'end_date'       => null,
                'salary'         => $this->genererSalaire($deptId, 'CDI'),
            ];
        });
    }

    public function cdd(): static
    {
        return $this->state(function () {
            $start  = Carbon::now()->subMonths(rand(1, 12))->startOfMonth();
            $dept   = Departement::inRandomOrder()->first();
            $deptId = $dept?->id ?? 1;
            $poste  = Poste::where('departement_id', $deptId)->inRandomOrder()->first();
            return [
                'contract_type'  => 'CDD',
                'departement_id' => $deptId,
                'poste_id'       => $poste?->id,
                'start_date'     => $start->toDateString(),
                'end_date'       => $start->copy()->addMonths(rand(6, 24))->toDateString(),
                'salary'         => $this->genererSalaire($deptId, 'CDD'),
            ];
        });
    }

    public function civp(): static
    {
        return $this->state(function () {
            $start  = Carbon::now()->subMonths(rand(1, 6))->startOfMonth();
            $dept   = Departement::inRandomOrder()->first();
            $deptId = $dept?->id ?? 1;
            $poste  = Poste::where('departement_id', $deptId)->inRandomOrder()->first();
            return [
                'contract_type'  => 'CIVP',
                'departement_id' => $deptId,
                'poste_id'       => $poste?->id,
                'start_date'     => $start->toDateString(),
                'end_date'       => $start->copy()->addMonths(rand(6, 12))->toDateString(),
                'salary'         => $this->genererSalaire($deptId, 'CIVP'),
            ];
        });
    }

    public function karama(): static
    {
        return $this->state(function () {
            $start  = Carbon::now()->subMonths(rand(1, 6))->startOfMonth();
            $dept   = Departement::inRandomOrder()->first();
            $deptId = $dept?->id ?? 1;
            $poste  = Poste::where('departement_id', $deptId)->inRandomOrder()->first();
            return [
                'contract_type'  => 'Karama',
                'departement_id' => $deptId,
                'poste_id'       => $poste?->id,
                'start_date'     => $start->toDateString(),
                'end_date'       => $start->copy()->addMonths(rand(6, 12))->toDateString(),
                'salary'         => $this->faker->numberBetween(200, 800),
            ];
        });
    }
}