<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\Departement;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $postes = [
            'Direction'          => ['Directeur Général', 'Directeur Adjoint', 'Assistant de Direction'],
            'Ressources Humaines'=> ['Responsable RH', 'Chargé de Recrutement', 'Gestionnaire Paie', 'Assistant RH'],
            'Finance'            => ['Directeur Financier', 'Comptable', 'Contrôleur de Gestion', 'Assistant Comptable'],
            'Commercial'         => ['Directeur Commercial', 'Commercial', 'Chargé de Clientèle', 'Responsable Marketing'],
            'Informatique'       => ['Développeur Full Stack', 'Développeur Backend', 'Développeur Frontend', 'Administrateur Système', 'Chef de Projet IT'],
            'Production'         => ['Responsable Production', 'Chef d\'Équipe', 'Technicien', 'Opérateur'],
            'Logistique'         => ['Responsable Logistique', 'Magasinier', 'Chauffeur', 'Agent de Transit'],
            'Qualité'            => ['Responsable Qualité', 'Auditeur', 'Technicien Qualité', 'Contrôleur Qualité'],
        ];

        foreach ($postes as $deptName => $listePostes) {
            $dept = Departement::where('name', $deptName)->first();
            if (!$dept) {
                $this->command->warn("Département introuvable : {$deptName}");
                continue;
            }
            foreach ($listePostes as $posteName) {
                Post::firstOrCreate(
                    ['name' => $posteName, 'department_id' => $dept->id],
                    ['description' => null]
                );
            }
        }

        $this->command->info('✓ Postes créés.');
    }
}