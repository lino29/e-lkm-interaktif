<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EditorImageUploadController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'upload' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Gambar editor tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $path = $validated['upload']->store('editor-images', 'public');
        $url = Storage::disk('public')->url($path);

        return response()->json([
            'uploaded' => 1,
            'url' => $url,
            'default' => $url,
        ]);
    }
}
