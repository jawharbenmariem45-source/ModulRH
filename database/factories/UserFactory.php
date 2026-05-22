<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Company;
use App\Models\Departement;
use App\Models\Post;
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

    // Plages de salaires CDI/CDD par département (SMIG 2026 = 470 TND)
    private static array $salaires = [
        1 => [1000, 4000], // Direction
        2 => [600,  2500], // RH
        3 => [700,  3000], // Finance
        4 => [500,  2500], // Commercial
        5 => [800,  3500], // Informatique
        6 => [470,  1800], // Production
        7 => [470,  1600], // Logistique
        8 => [500,  2000], // Qualité
    ];

    private static array $domaines = [
        1 => 'alphacorp.tn',
        2 => 'technova.tn',
        3 => 'summitrise.tn',
    ];

    private static int $compteur = 1;

    // ── Générateurs ───────────────────────────────────────────────────────────

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
        // CIVP : part entreprise entre 200 et 400 TND
        // (l'ANETI ajoute 200 TND → net stagiaire entre 400 et 600 TND)
        if ($contractType === 'CIVP') return $this->faker->numberBetween(200, 400);

        // Karama : part employeur entre 200 et 800 TND selon profil
        // + 400 TND État → net entre 600 et 1200 TND
        if ($contractType === 'Karama') return $this->faker->numberBetween(200, 800);

        // CDI / CDD : selon grille par département
        $plage = self::$salaires[$deptId] ?? [470, 2000];
        return $this->faker->numberBetween($plage[0], $plage[1]);
    }

    private function genererDisciplineScore(): int
    {
        $rand = rand(1, 100);
        if ($rand <= 40) return rand(90, 100); // 40% bons employés
        if ($rand <= 70) return rand(70, 89);  // 30% moyens
        if ($rand <= 90) return rand(50, 69);  // 20% passables
        return rand(20, 49);                   // 10% mauvais
    }

    // ── Definition ────────────────────────────────────────────────────────────

    public function definition(): array
    {
        $company      = Company::inRandomOrder()->first();
        $dept         = Departement::inRandomOrder()->first();
        $companyId    = $company?->id ?? 1;
        $deptId       = $dept?->id ?? 1;
        $contractType = $this->faker->randomElement(['CDI', 'CDD', 'CIVP', 'Karama']);

        $post       = Post::where('department_id', $deptId)->inRandomOrder()->first();
        $schedule   = Schedule::inRandomOrder()->first();

        $prenom  = $this->genererPrenom();
        $nom     = $this->genererNom();
        $domaine = self::$domaines[$companyId] ?? 'entreprise.tn';

        $prenomAscii = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $prenom));
        $nomAscii    = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', explode(' ', $nom)[0]));
        $compteur    = self::$compteur++;
        $email       = $prenomAscii . '.' . $nomAscii . '.' . $compteur . '@' . $domaine;

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
            'department_id'           => $deptId,
            'company_id'              => $companyId,
            'post_id'                 => $post?->id,
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
            'start_date'              => $startDate->format('Y-m-d'),
            'end_date'                => $endDate?->format('Y-m-d'),
            'remember_token'          => Str::random(10),
        ];
    }

    // ── États ─────────────────────────────────────────────────────────────────

    public function unverified(): static
    {
        return $this->state(fn() => ['email_verified_at' => null]);
    }

    public function ancien(): static
    {
        return $this->state(function () {
            $dept   = Departement::inRandomOrder()->first();
            $deptId = $dept?->id ?? 1;
            $post   = Post::where('department_id', $deptId)->inRandomOrder()->first();
            return [
                'contract_type'    => 'CDI',
                'department_id'    => $deptId,
                'post_id'          => $post?->id,
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
            $post   = Post::where('department_id', $deptId)->inRandomOrder()->first();
            return [
                'contract_type' => 'CDI',
                'department_id' => $deptId,
                'post_id'       => $post?->id,
                'end_date'      => null,
                'salary'        => $this->genererSalaire($deptId, 'CDI'),
            ];
        });
    }

    public function cdd(): static
    {
        return $this->state(function () {
            $start  = Carbon::now()->subMonths(rand(1, 12))->startOfMonth();
            $dept   = Departement::inRandomOrder()->first();
            $deptId = $dept?->id ?? 1;
            $post   = Post::where('department_id', $deptId)->inRandomOrder()->first();
            return [
                'contract_type' => 'CDD',
                'department_id' => $deptId,
                'post_id'       => $post?->id,
                'start_date'    => $start->toDateString(),
                'end_date'      => $start->copy()->addMonths(rand(6, 24))->toDateString(),
                'salary'        => $this->genererSalaire($deptId, 'CDD'),
            ];
        });
    }

    public function civp(): static
    {
        return $this->state(function () {
            $start  = Carbon::now()->subMonths(rand(1, 6))->startOfMonth();
            $dept   = Departement::inRandomOrder()->first();
            $deptId = $dept?->id ?? 1;
            $post   = Post::where('department_id', $deptId)->inRandomOrder()->first();
            return [
                'contract_type' => 'CIVP',
                'department_id' => $deptId,
                'post_id'       => $post?->id,
                'start_date'    => $start->toDateString(),
                'end_date'      => $start->copy()->addMonths(rand(6, 12))->toDateString(),
                'salary'        => $this->genererSalaire($deptId, 'CIVP'),
            ];
        });
    }

    public function karama(): static
    {
        return $this->state(function () {
            $start  = Carbon::now()->subMonths(rand(1, 6))->startOfMonth();
            $dept   = Departement::inRandomOrder()->first();
            $deptId = $dept?->id ?? 1;
            $post   = Post::where('department_id', $deptId)->inRandomOrder()->first();
            return [
                'contract_type' => 'Karama',
                'department_id' => $deptId,
                'post_id'       => $post?->id,
                'start_date'    => $start->toDateString(),
                'end_date'      => $start->copy()->addMonths(rand(6, 12))->toDateString(),
                'salary'        => $this->faker->numberBetween(200, 800),
            ];
        });
    }
}