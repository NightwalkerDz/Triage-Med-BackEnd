<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Triage;
use App\Models\UrgencyLevel;
use App\Services\TriageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UrgencyLevelController extends Controller
{
    public function __construct(private TriageService $triageService)
    {
    }

    public function index(): JsonResponse
    {
        $levels = $this->triageService->tousLesNiveaux()
            ->map(fn (UrgencyLevel $level) => array_merge(
                $this->triageService->formatLevel($level),
                ['symptoms_count' => $level->symptoms()->count()]
            ));

        return response()->json($levels);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateLevel($request);

        $level = UrgencyLevel::create($validated);
        $this->triageService->clearCache();

        return response()->json($this->triageService->formatLevel($level), 201);
    }

    public function update(Request $request, UrgencyLevel $urgencyLevel): JsonResponse
    {
        $validated = $this->validateLevel($request, $urgencyLevel->id);

        $urgencyLevel->update($validated);
        $this->triageService->clearCache();

        return response()->json($this->triageService->formatLevel($urgencyLevel->fresh()));
    }

    public function destroy(UrgencyLevel $urgencyLevel): JsonResponse
    {
        if ($urgencyLevel->symptoms()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer un niveau qui contient des symptômes. Désactivez-le ou déplacez les symptômes.',
            ], 422);
        }

        if (Triage::query()->where('niveau_urgence', $urgencyLevel->code)->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer un niveau utilisé dans des triages existants. Désactivez-le plutôt.',
            ], 422);
        }

        $urgencyLevel->delete();
        $this->triageService->clearCache();

        return response()->json(['message' => 'Niveau d\'urgence supprimé.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateLevel(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('urgency_levels', 'code')->ignore($ignoreId),
            ],
            'label' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'integer', 'min:1', 'max:99'],
            'emoji' => ['nullable', 'string', 'max:10'],
            'theme' => ['required', 'string', Rule::in(['red', 'orange', 'emerald', 'blue', 'purple', 'slate'])],
            'regle_appliquee' => ['nullable', 'string'],
            'actif' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);
    }
}
