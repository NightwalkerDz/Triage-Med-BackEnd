<?php

namespace App\Services;

use App\Models\Symptom;
use App\Models\UrgencyLevel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TriageService
{
    public const NIVEAU_IMMEDIAT = 'immediat';

    public const NIVEAU_MODERE = 'modere';

    public const NIVEAU_NON_URGENT = 'non_urgent';

    public const SYMPTOMES_IMMEDIAT = [
        'difficulte_respiratoire_severe' => 'Difficulté respiratoire sévère',
        'douleur_thoracique_intense' => 'Douleur thoracique intense',
        'perte_de_connaissance' => 'Perte de connaissance',
        'convulsions' => 'Convulsions',
        'hemorragie_importante' => 'Hémorragie importante',
        'cyanose' => 'Cyanose (lèvres bleues)',
        'paralysie_soudaine' => 'Paralysie soudaine',
        'confusion_mentale_severe' => 'Confusion mentale sévère',
        'saturation_oxygene_basse' => 'Saturation en oxygène basse',
        'choc' => 'Choc (TA basse, peau froide, sueurs)',
    ];

    public const SYMPTOMES_MODERE = [
        'douleur_moderee' => 'Douleur modérée',
        'toux_fievre' => 'Toux avec fièvre',
        'vertiges' => 'Vertiges',
        'infection_urinaire' => 'Infection urinaire',
        'fatigue_importante' => 'Fatigue importante',
        'diarrhee_sans_gravite' => 'Diarrhée sans gravité',
    ];

    public const SYMPTOMES_NON_URGENT = [
        'rhume_simple' => 'Rhume simple',
        'petit_mal_de_tete' => 'Petit mal de tête',
        'douleurs_chroniques_stables' => 'Douleurs chroniques stables',
        'renouvellement_ordonnance' => 'Renouvellement d\'ordonnance',
        'petites_blessures_superficielles' => 'Petites blessures superficielles',
    ];

    private const CACHE_KEY = 'triage_config_v1';

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array{levels: array<int, array<string, mixed>>, catalog: array<string, array<string, string>>}
     */
    public function config(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            return [
                'levels' => $this->niveauxActifs()->map(fn (UrgencyLevel $level) => $this->formatLevel($level))->values()->all(),
                'catalog' => $this->tousLesSymptomes(),
            ];
        });
    }

    /**
     * @return Collection<int, UrgencyLevel>
     */
    public function niveauxActifs(): Collection
    {
        return UrgencyLevel::query()
            ->where('actif', true)
            ->orderBy('priority')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @return Collection<int, UrgencyLevel>
     */
    public function tousLesNiveaux(): Collection
    {
        return UrgencyLevel::query()
            ->orderBy('priority')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function tousLesSymptomes(): array
    {
        $catalog = [];

        foreach ($this->niveauxActifs() as $level) {
            $catalog[$level->code] = $this->symptomesPourNiveau($level);
        }

        return $catalog;
    }

    /**
     * @return array<string, string>
     */
    public function symptomesPourNiveau(UrgencyLevel $level): array
    {
        return Symptom::query()
            ->where('urgency_level_id', $level->id)
            ->where('actif', true)
            ->orderBy('sort_order')
            ->pluck('label', 'code')
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function cataloguePlat(): array
    {
        $catalog = [];
        foreach ($this->tousLesSymptomes() as $symptoms) {
            foreach ($symptoms as $code => $label) {
                $catalog[$code] = $label;
            }
        }

        return $catalog;
    }

    /**
     * @param  array<string>  $symptomes
     * @return array{niveau_urgence: string, symptomes_declencheurs: array<string, string>, regle_appliquee: string}
     */
    public function evaluer(array $symptomes): array
    {
        $symptomes = array_values(array_unique(array_filter($symptomes)));
        $niveaux = $this->niveauxActifs();

        if ($niveaux->isEmpty()) {
            return [
                'niveau_urgence' => self::NIVEAU_NON_URGENT,
                'symptomes_declencheurs' => [],
                'regle_appliquee' => 'Aucun niveau d\'urgence configuré.',
            ];
        }

        foreach ($niveaux as $level) {
            $catalogue = $this->symptomesPourNiveau($level);
            $declencheurs = $this->symptomesDeclencheurs($symptomes, $catalogue);

            if (count($declencheurs) > 0) {
                return [
                    'niveau_urgence' => $level->code,
                    'symptomes_declencheurs' => $declencheurs,
                    'regle_appliquee' => $level->regle_appliquee
                        ?? 'Niveau '.$level->label.' déterminé selon les symptômes sélectionnés.',
                ];
            }
        }

        $defaut = $niveaux->sortByDesc('priority')->first();

        return [
            'niveau_urgence' => $defaut->code,
            'symptomes_declencheurs' => [],
            'regle_appliquee' => 'Aucun symptôme sélectionné ou symptômes non reconnus : classé par défaut en '.$defaut->label.'.',
        ];
    }

    public function libelleNiveau(string $niveau): string
    {
        $level = UrgencyLevel::query()->where('code', $niveau)->first();

        return $level?->label ?? $niveau;
    }

    public function prioriteTri(string $niveau): int
    {
        $level = UrgencyLevel::query()->where('code', $niveau)->first();

        return $level?->priority ?? 999;
    }

    /**
     * @return array<int, string>
     */
    public function codesNiveauxActifs(): array
    {
        return $this->niveauxActifs()->pluck('code')->all();
    }

    public function niveauExiste(string $code): bool
    {
        return UrgencyLevel::query()->where('code', $code)->where('actif', true)->exists();
    }

    /**
     * @param  array<string>  $symptomes
     * @param  array<string, string>  $catalogue
     * @return array<string, string>
     */
    private function symptomesDeclencheurs(array $symptomes, array $catalogue): array
    {
        $declencheurs = [];
        foreach ($symptomes as $code) {
            if (isset($catalogue[$code])) {
                $declencheurs[$code] = $catalogue[$code];
            }
        }

        return $declencheurs;
    }

    /**
     * @return array<string, mixed>
     */
    public function formatLevel(UrgencyLevel $level): array
    {
        return [
            'id' => $level->id,
            'code' => $level->code,
            'label' => $level->label,
            'priority' => $level->priority,
            'emoji' => $level->emoji,
            'theme' => $level->theme,
            'regle_appliquee' => $level->regle_appliquee,
            'actif' => $level->actif,
            'sort_order' => $level->sort_order,
        ];
    }
}
