<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medecin;
use App\Models\Patient;
use App\Models\Triage;
use App\Services\TriageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    public function __construct(private TriageService $triageService)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $codesActifs = $this->triageService->codesNiveauxActifs();

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'age' => ['required', 'integer', 'min:0', 'max:150'],
            'sexe' => ['required', 'in:M,F'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'antecedents_medicaux' => ['nullable', 'string'],
            'medecin_traitant' => ['nullable', 'string', 'max:255'],
            'medecin_id' => ['nullable', 'exists:medecins,id'],
            'symptomes' => ['required', 'array'],
            'symptomes.*' => ['string'],
            'niveau_urgence' => ['nullable', 'string', Rule::in($codesActifs)],
        ]);

        $evaluation = $this->triageService->evaluer($validated['symptomes']);
        $niveauUrgence = $evaluation['niveau_urgence'];

        $medecinTraitant = $validated['medecin_traitant'] ?? null;
        if (! empty($validated['medecin_id'])) {
            $medecin = Medecin::find($validated['medecin_id']);
            $medecinTraitant = $medecin?->nom_complet ?? $medecinTraitant;
        }

        $patient = DB::transaction(function () use ($validated, $medecinTraitant, $niveauUrgence) {
            $patient = Patient::create([
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'age' => $validated['age'],
                'sexe' => $validated['sexe'],
                'telephone' => $validated['telephone'] ?? null,
                'antecedents_medicaux' => $validated['antecedents_medicaux'] ?? null,
                'medecin_traitant' => $medecinTraitant,
                'medecin_id' => $validated['medecin_id'] ?? null,
            ]);

            Triage::create([
                'patient_id' => $patient->id,
                'symptomes' => $validated['symptomes'],
                'niveau_urgence' => $niveauUrgence,
            ]);

            return $patient;
        });

        return response()->json($this->formatPatient($patient->load(['triage', 'medecin'])), 201);
    }

    public function index(Request $request): JsonResponse
    {
        $query = Patient::with(['triage', 'medecin']);

        if ($request->filled('niveau_urgence')) {
            $niveau = $request->string('niveau_urgence');
            $query->whereHas('triage', function ($q) use ($niveau) {
                $q->where('niveau_urgence', $niveau);
            });
        }

        $patients = $query->get()
            ->sort(function (Patient $a, Patient $b) {
                $prioriteA = $this->triageService->prioriteTri($a->triage?->niveau_urgence ?? '');
                $prioriteB = $this->triageService->prioriteTri($b->triage?->niveau_urgence ?? '');

                if ($prioriteA !== $prioriteB) {
                    return $prioriteA <=> $prioriteB;
                }

                return $b->created_at <=> $a->created_at;
            })
            ->values()
            ->map(fn (Patient $patient) => $this->formatPatientList($patient));

        return response()->json($patients);
    }

    public function show(Patient $patient): JsonResponse
    {
        $patient->load(['triage', 'medecin']);

        return response()->json($this->formatPatientDetail($patient));
    }

    public function destroy(Patient $patient): JsonResponse
    {
        $patient->delete();

        $this->triageService->clearCache();

        return response()->json(['message' => 'Patient supprimé avec succès.']);
    }

    private function formatPatientList(Patient $patient): array
    {
        return [
            'id' => $patient->id,
            'nom' => $patient->nom,
            'prenom' => $patient->prenom,
            'age' => $patient->age,
            'sexe' => $patient->sexe,
            'niveau_urgence' => $patient->triage?->niveau_urgence,
            'niveau_urgence_libelle' => $this->triageService->libelleNiveau($patient->triage?->niveau_urgence ?? ''),
            'heure_arrivee' => $patient->created_at?->toIso8601String(),
            'created_at' => $patient->created_at?->toIso8601String(),
        ];
    }

    private function formatPatient(Patient $patient): array
    {
        return $this->formatPatientDetail($patient);
    }

    private function formatPatientDetail(Patient $patient): array
    {
        $symptomes = $patient->triage?->symptomes ?? [];
        $evaluation = $this->triageService->evaluer($symptomes);
        $symptomesLibelles = $this->symptomesAvecLibelles($symptomes);

        return [
            'id' => $patient->id,
            'nom' => $patient->nom,
            'prenom' => $patient->prenom,
            'age' => $patient->age,
            'sexe' => $patient->sexe,
            'telephone' => $patient->telephone,
            'antecedents_medicaux' => $patient->antecedents_medicaux,
            'medecin_traitant' => $patient->medecin_traitant,
            'medecin_id' => $patient->medecin_id,
            'medecin' => $patient->medecin ? [
                'id' => $patient->medecin->id,
                'nom_complet' => $patient->medecin->nom_complet,
                'specialite' => $patient->medecin->specialite,
            ] : null,
            'created_at' => $patient->created_at?->toIso8601String(),
            'triage' => [
                'id' => $patient->triage?->id,
                'symptomes' => $symptomes,
                'symptomes_libelles' => $symptomesLibelles,
                'niveau_urgence' => $patient->triage?->niveau_urgence,
                'niveau_urgence_libelle' => $this->triageService->libelleNiveau($patient->triage?->niveau_urgence ?? ''),
                'symptomes_declencheurs' => $evaluation['symptomes_declencheurs'],
                'regle_appliquee' => $evaluation['regle_appliquee'],
                'created_at' => $patient->triage?->created_at?->toIso8601String(),
            ],
        ];
    }

    /**
     * @param  array<string>  $symptomes
     * @return array<string, string>
     */
    private function symptomesAvecLibelles(array $symptomes): array
    {
        $catalogue = $this->triageService->cataloguePlat();

        $libelles = [];
        foreach ($symptomes as $code) {
            $libelles[$code] = $catalogue[$code] ?? $code;
        }

        return $libelles;
    }
}
