<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EditorImageUploadController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'upload' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $path = $validated['upload']->store('editor-images', 'public');
        $url = Storage::disk('public')->url($path);

        return response()->json([
            'uploaded' => 1,
            'url' => $url,
            'default' => $url,
        ]);
    }
}
