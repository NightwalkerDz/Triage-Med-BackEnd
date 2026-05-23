<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Symptom;
use App\Models\UrgencyLevel;
use App\Services\TriageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SymptomController extends Controller
{
    public function __construct(private TriageService $triageService)
    {
    }

    public function index(): JsonResponse
    {
        $symptoms = Symptom::query()
            ->with('urgencyLevel:id,code,label')
            ->orderBy('urgency_level_id')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Symptom $symptom) => $this->formatSymptom($symptom));

        return response()->json($symptoms);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateSymptom($request);

        $symptom = Symptom::create($validated);
        $symptom->load('urgencyLevel:id,code,label');
        $this->triageService->clearCache();

        return response()->json($this->formatSymptom($symptom), 201);
    }

    public function update(Request $request, Symptom $symptom): JsonResponse
    {
        $validated = $this->validateSymptom($request, $symptom->id);

        $symptom->update($validated);
        $symptom->load('urgencyLevel:id,code,label');
        $this->triageService->clearCache();

        return response()->json($this->formatSymptom($symptom));
    }

    public function destroy(Symptom $symptom): JsonResponse
    {
        $symptom->delete();
        $this->triageService->clearCache();

        return response()->json(['message' => 'Symptôme supprimé.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateSymptom(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:80',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('symptoms', 'code')->ignore($ignoreId),
            ],
            'label' => ['required', 'string', 'max:255'],
            'urgency_level_id' => ['required', 'exists:urgency_levels,id'],
            'actif' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatSymptom(Symptom $symptom): array
    {
        return [
            'id' => $symptom->id,
            'code' => $symptom->code,
            'label' => $symptom->label,
            'urgency_level_id' => $symptom->urgency_level_id,
            'urgency_level' => $symptom->urgencyLevel ? [
                'id' => $symptom->urgencyLevel->id,
                'code' => $symptom->urgencyLevel->code,
                'label' => $symptom->urgencyLevel->label,
            ] : null,
            'actif' => $symptom->actif,
            'sort_order' => $symptom->sort_order,
        ];
    }
}
