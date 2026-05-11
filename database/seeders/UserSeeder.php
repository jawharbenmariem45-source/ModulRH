<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Company;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $company1 = Company::where('name', 'SummitRise')->first();
        

        // Admin — pas de company
        $admin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'       => 'Admin',
                'password'   => Hash::make('azerty'),
                'company_id' => null,
            ]
        );
        $admin->syncRoles(['admin']);

        // RH — lié à SummitRise
        $rh = User::updateOrCreate(
            ['email' => 'rh@gmail.com'],
            [
                'name'       => 'rh',
                'password'   => Hash::make('123456'),
                'company_id' => $company1->id,
            ]
        );
        $rh->syncRoles(['rh']);

        // Manager — lié à SummitRise
        $manager = User::updateOrCreate(
            ['email' => 'manager@gmail.com'],
            [
                'name'       => 'Manager',
                'password'   => Hash::make('123456'),
                'company_id' => $company1->id,
            ]
        );
        $manager->syncRoles(['manager']);
    }
}