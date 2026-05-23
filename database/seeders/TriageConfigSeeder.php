<?php

namespace Database\Seeders;

use App\Models\Symptom;
use App\Models\UrgencyLevel;
use App\Services\TriageService;
use Illuminate\Database\Seeder;

class TriageConfigSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            [
                'code' => TriageService::NIVEAU_IMMEDIAT,
                'label' => 'Urgence immédiate',
                'priority' => 1,
                'emoji' => '🔴',
                'theme' => 'red',
                'regle_appliquee' => 'Au moins un symptôme d\'urgence immédiate a été identifié. Prise en charge prioritaire requise.',
                'sort_order' => 1,
                'symptoms' => TriageService::SYMPTOMES_IMMEDIAT,
            ],
            [
                'code' => TriageService::NIVEAU_MODERE,
                'label' => 'Urgence modérée',
                'priority' => 2,
                'emoji' => '🟠',
                'theme' => 'orange',
                'regle_appliquee' => 'Aucun symptôme rouge, mais au moins un symptôme d\'urgence modérée. Surveillance et prise en charge dans un délai raisonnable.',
                'sort_order' => 2,
                'symptoms' => TriageService::SYMPTOMES_MODERE,
            ],
            [
                'code' => TriageService::NIVEAU_NON_URGENT,
                'label' => 'Non urgent',
                'priority' => 3,
                'emoji' => '🟢',
                'theme' => 'emerald',
                'regle_appliquee' => 'Cas non urgent : symptômes bénins ou stables sans signe de gravité.',
                'sort_order' => 3,
                'symptoms' => TriageService::SYMPTOMES_NON_URGENT,
            ],
        ];

        foreach ($levels as $index => $levelData) {
            $symptoms = $levelData['symptoms'];
            unset($levelData['symptoms']);

            $level = UrgencyLevel::updateOrCreate(
                ['code' => $levelData['code']],
                array_merge($levelData, ['actif' => true])
            );

            $sort = 0;
            foreach ($symptoms as $code => $label) {
                $sort++;
                Symptom::updateOrCreate(
                    ['code' => $code],
                    [
                        'label' => $label,
                        'urgency_level_id' => $level->id,
                        'actif' => true,
                        'sort_order' => $sort,
                    ]
                );
            }
        }
    }
}
