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
            ['nom' => 'بن سعيد', 'prenom' => 'عبد الرحمان', 'specialite' => 'Médecine générale', 'email' => 'a.bensaid@clinique.dz', 'telephone' => '05 60 12 34 01'],
            ['nom' => 'بوجمعة', 'prenom' => 'فاطمة', 'specialite' => 'Urgences', 'email' => 'f.boujemaa@clinique.dz', 'telephone' => '05 60 12 34 02'],
            ['nom' => 'حداد', 'prenom' => 'أحمد', 'specialite' => 'Cardiologie', 'email' => 'a.haddad@clinique.dz', 'telephone' => '05 60 12 34 03'],
            ['nom' => 'مخلوفي', 'prenom' => 'نورة', 'specialite' => 'Pédiatrie', 'email' => 'n.mekhloufi@clinique.dz', 'telephone' => '05 60 12 34 04'],
        ];

        foreach ($medecins as $medecin) {
            Medecin::updateOrCreate(
                ['email' => $medecin['email']],
                array_merge($medecin, ['actif' => true])
            );
        }
    }
}
