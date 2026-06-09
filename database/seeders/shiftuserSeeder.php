<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Shift;

class ShiftUserSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = Shift::all();

        if ($shifts->isEmpty()) {
            $this->command->warn('Aucun shift trouvé — lancez d\'abord ShiftSeeder.');
            return;
        }

        $defaultShift = $shifts->where('is_default', true)->first() ?? $shifts->first();

        $users = User::role('employer')->get();

        if ($users->isEmpty()) {
            $this->command->warn('Aucun employé trouvé.');
            return;
        }

        $bar = $this->command->getOutput()->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            // Vérifier si déjà assigné
            $exists = DB::table('shift_user')
                ->where('user_id', $user->id)
                ->exists();

            if (!$exists) {
                // Assigner un shift aléatoire ou le shift lié au user
                $shift = $user->shift_id
                    ? $shifts->find($user->shift_id) ?? $defaultShift
                    : $shifts->random();

                DB::table('shift_user')->insert([
                    'user_id'    => $user->id,
                    'shift_id'   => $shift->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('✓ shift_user rempli pour ' . $users->count() . ' employés.');
    }
}