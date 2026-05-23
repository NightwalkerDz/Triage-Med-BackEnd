<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identifiant' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $identifiant = trim($validated['identifiant']);
        $password = $validated['password'];

        $user = User::query()
            ->where(function ($query) use ($identifiant) {
                $query->where('name', $identifiant)
                    ->orWhere('email', $identifiant);
            })
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'identifiant' => ['Identifiants incorrects.'],
            ]);
        }

        $user->tokens()->delete();

        $token = $user->createToken('triage-med-web')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnexion réussie.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json($this->formatUser($user));
    }

    /**
     * @return array<string, mixed>
     */
    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role ?? 'staff',
        ];
    }
}
