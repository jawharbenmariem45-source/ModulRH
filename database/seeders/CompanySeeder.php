<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::updateOrCreate(
            ['name' => 'AlphaCorp'],
            ['payment_date' => 28, 'work_schedule' => '48h']
        );

        Company::updateOrCreate(
            ['name' => 'TechNova'],
            ['payment_date' => 30, 'work_schedule' => '48h']
        );

        Company::updateOrCreate(
            ['name' => 'SummitRise'],
            ['payment_date' => 25, 'work_schedule' => '48h']
        );

        $this->command->info('✓ 3 companies créées');
    }
}