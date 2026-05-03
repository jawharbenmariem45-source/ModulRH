<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employer;
use App\Models\Contract;

class EmployerContractSeeder extends Seeder
{
    public function run(): void
    {
        $employers = Employer::all();

        foreach ($employers as $employer) {
            if ($employer->type_contrat) {
                $contract = Contract::where('name', $employer->type_contrat)->first();

                if ($contract) {
                    // Vérifier si le lien n'existe pas déjà
                    $exists = $employer->contracts()->where('contract_id', $contract->id)->exists();

                    if (!$exists) {
                        $employer->contracts()->attach($contract->id, [
                            'start_date' => $employer->date_debut,
                            'end_date'   => $employer->date_fin,
                        ]);
                    }
                }
            }
        }
    }
}