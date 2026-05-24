<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Triage;
use App\Services\TriageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TriageController extends Controller
{
    public function __construct(private TriageService $triageService)
    {
    }

    public function evaluer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'symptomes' => ['required', 'array'],
            'symptomes.*' => ['string'],
        ]);

        $resultat = $this->triageService->evaluer($validated['symptomes']);

        return response()->json([
            'niveau_urgence' => $resultat['niveau_urgence'],
            'niveau_urgence_libelle' => $this->triageService->libelleNiveau($resultat['niveau_urgence']),
            'symptomes_declencheurs' => $resultat['symptomes_declencheurs'],
            'regle_appliquee' => $resultat['regle_appliquee'],
        ]);
    }

    public function destroy(Triage $triage): JsonResponse
    {
        $triage->delete();

        $this->triageService->clearCache();

        return response()->json(['message' => 'Triage supprimé avec succès.']);
    }
}
