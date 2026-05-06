<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $configurations = [
            [
                'key'            => 'taux_cnss',
                'type'           => 'ANOTHER',
                'regime_horaire' => '40h',
                'value'          => '9.18',
                'company_id'     => null,
            ],
            [
                'key'            => 'nom_application',
                'type'           => 'APP_NAME',
                'regime_horaire' => '40h',
                'value'          => 'RiseTruck',
                'company_id'     => null,
            ],
            [
                'key'            => 'devise',
                'type'           => 'ANOTHER',
                'regime_horaire' => '40h',
                'value'          => 'TND',
                'company_id'     => null,
            ],
            [
                'key'            => 'smig_regime_48h',
                'type'           => 'REGIME_HORAIRE',
                'regime_horaire' => '48h',
                'value'          => '460.500',
                'company_id'     => null,
            ],
            [
                'key'            => 'smig_regime_40h',
                'type'           => 'REGIME_HORAIRE',
                'regime_horaire' => '40h',
                'value'          => '380.000',
                'company_id'     => null,
            ],
            [
                'key'            => 'payment_date',
                'type'           => 'PAYMENT_DATEE',
                'regime_horaire' => '40h',
                'value'          => '30',
                'company_id'     => null,
            ],
            [
                'key'            => 'developper_name',
                'type'           => 'DEVELOPPER_NAME',
                'regime_horaire' => '40h',
                'value'          => 'RiseTruck',
                'company_id'     => null,
            ],
        ];

        foreach ($configurations as $config) {
            DB::table('configurations')->updateOrInsert(
                ['key' => $config['key']],
                array_merge($config, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}