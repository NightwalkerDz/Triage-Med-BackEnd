<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medecin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedecinController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Medecin::query()->orderBy('nom')->orderBy('prenom');

        if ($request->boolean('actif_only', true)) {
            $query->where('actif', true);
        }

        $medecins = $query->get()->map(fn (Medecin $m) => $this->formatMedecin($m));

        return response()->json($medecins);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'specialite' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'actif' => ['sometimes', 'boolean'],
        ]);

        $medecin = Medecin::create($validated);

        return response()->json($this->formatMedecin($medecin), 201);
    }

    public function update(Request $request, Medecin $medecin): JsonResponse
    {
        $validated = $request->validate([
            'nom' => ['sometimes', 'string', 'max:255'],
            'prenom' => ['sometimes', 'string', 'max:255'],
            'specialite' => ['sometimes', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'actif' => ['sometimes', 'boolean'],
        ]);

        $medecin->update($validated);

        return response()->json($this->formatMedecin($medecin->fresh()));
    }

    public function destroy(Medecin $medecin): JsonResponse
    {
        $medecin->update(['actif' => false]);

        return response()->json(['message' => 'Médecin désactivé.']);
    }

    private function formatMedecin(Medecin $medecin): array
    {
        return [
            'id' => $medecin->id,
            'nom' => $medecin->nom,
            'prenom' => $medecin->prenom,
            'nom_complet' => $medecin->nom_complet,
            'specialite' => $medecin->specialite,
            'email' => $medecin->email,
            'telephone' => $medecin->telephone,
            'actif' => $medecin->actif,
            'created_at' => $medecin->created_at?->toIso8601String(),
        ];
    }
}
