<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Company;

class ConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            DB::table('configurations')->updateOrInsert(
                ['company_id' => $company->id],
                [
                    'payment_date'   => 30,
                    'regime_horaire' => '40h',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]
            );
        }

        $this->command->info('✓ Configurations créées pour ' . $companies->count() . ' companies.');
    }
}