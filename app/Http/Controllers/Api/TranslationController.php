<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GoogleTranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    /**
     * Translate single text string dynamically for partner/admin forms.
     */
    public function translate(Request $request, GoogleTranslationService $service): JsonResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:5000'],
            'target' => ['nullable', 'string', 'in:en,id'],
            'source' => ['nullable', 'string', 'in:id,en'],
        ]);

        $translated = $service->translate(
            text: $validated['text'],
            target: $validated['target'] ?? 'en',
            source: $validated['source'] ?? 'id'
        );

        return response()->json([
            'success' => true,
            'translated' => $translated,
        ]);
    }

    /**
     * Translate batch key-value fields (e.g. name, short_description, full_description).
     */
    public function translateBatch(Request $request, GoogleTranslationService $service): JsonResponse
    {
        $validated = $request->validate([
            'fields' => ['required', 'array'],
            'fields.*' => ['nullable', 'string', 'max:10000'],
            'target' => ['nullable', 'string', 'in:en,id'],
            'source' => ['nullable', 'string', 'in:id,en'],
        ]);

        $translated = $service->translateBatch(
            texts: $validated['fields'],
            target: $validated['target'] ?? 'en',
            source: $validated['source'] ?? 'id'
        );

        return response()->json([
            'success' => true,
            'translations' => $translated,
        ]);
    }
}

