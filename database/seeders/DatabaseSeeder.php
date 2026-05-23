<?php

namespace Database\Seeders;

use App\Models\Medecin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@triage-med.fr'],
            [
                'name' => 'admin',
                'password' => Hash::make('admin'),
                'role' => 'admin',
            ]
        );

        $this->call(TriageConfigSeeder::class);

        $medecins = [
            ['nom' => 'Martin', 'prenom' => 'Sophie', 'specialite' => 'Médecine générale', 'email' => 's.martin@clinique.fr', 'telephone' => '01 23 45 67 01'],
            ['nom' => 'Bernard', 'prenom' => 'Jean', 'specialite' => 'Urgences', 'email' => 'j.bernard@clinique.fr', 'telephone' => '01 23 45 67 02'],
            ['nom' => 'Dubois', 'prenom' => 'Marie', 'specialite' => 'Cardiologie', 'email' => 'm.dubois@clinique.fr', 'telephone' => '01 23 45 67 03'],
            ['nom' => 'Petit', 'prenom' => 'Luc', 'specialite' => 'Pédiatrie', 'email' => 'l.petit@clinique.fr', 'telephone' => '01 23 45 67 04'],
        ];

        foreach ($medecins as $medecin) {
            Medecin::updateOrCreate(
                ['email' => $medecin['email']],
                array_merge($medecin, ['actif' => true])
            );
        }
    }
}
