<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TriageService;
use Illuminate\Http\JsonResponse;

class SymptomController extends Controller
{
    public function __construct(private TriageService $triageService)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->triageService->config());
    }
}
