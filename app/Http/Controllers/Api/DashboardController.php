<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medecin;
use App\Models\Patient;
use App\Models\Triage;
use App\Services\TriageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(private TriageService $triageService)
    {
    }

    public function index(): JsonResponse
    {
        $today = now()->startOfDay();

        $parUrgence = Triage::query()
            ->select('niveau_urgence', DB::raw('count(*) as total'))
            ->groupBy('niveau_urgence')
            ->pluck('total', 'niveau_urgence');

        $aujourdhui = Patient::where('created_at', '>=', $today)->count();
        $levels = $this->triageService->niveauxActifs();

        $urgenceCounts = [];
        foreach ($levels as $level) {
            $urgenceCounts[$level->code] = (int) ($parUrgence[$level->code] ?? 0);
        }

        foreach ($parUrgence as $code => $total) {
            if (! isset($urgenceCounts[$code])) {
                $urgenceCounts[$code] = (int) $total;
            }
        }

        $recent = Patient::with(['triage', 'medecin'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function (Patient $patient) {
                return [
                    'id' => $patient->id,
                    'nom' => $patient->nom,
                    'prenom' => $patient->prenom,
                    'niveau_urgence' => $patient->triage?->niveau_urgence,
                    'niveau_urgence_libelle' => $this->triageService->libelleNiveau(
                        $patient->triage?->niveau_urgence ?? ''
                    ),
                    'heure_arrivee' => $patient->created_at?->toIso8601String(),
                    'medecin' => $patient->medecin?->nom_complet ?? $patient->medecin_traitant,
                ];
            });

        return response()->json([
            'stats' => [
                'total_patients' => Patient::count(),
                'patients_aujourdhui' => $aujourdhui,
                'total_medecins' => Medecin::where('actif', true)->count(),
                'urgence_counts' => $urgenceCounts,
            ],
            'urgency_levels' => $levels->map(fn ($level) => $this->triageService->formatLevel($level))->values(),
            'recent_patients' => $recent,
        ]);
    }
}
