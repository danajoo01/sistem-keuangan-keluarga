<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AttachmentController extends Controller
{
    public function preview(Request $request): BinaryFileResponse
    {
        return $this->serve($request, false);
    }

    public function download(Request $request): BinaryFileResponse
    {
        return $this->serve($request, true);
    }

    private function serve(Request $request, bool $download): BinaryFileResponse
    {
        $validated = $request->validate([
            'disk' => ['required', 'string', 'in:public,local'],
            'path' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'owner' => ['nullable', 'integer'],
        ]);

        $ownerId = isset($validated['owner']) ? (int) $validated['owner'] : null;

        abort_unless($request->user()?->isAdmin() || ($ownerId !== null && (int) $request->user()?->id === $ownerId), 403);

        $disk = Storage::disk($validated['disk']);

        abort_unless($disk->exists($validated['path']), 404);

        $absolutePath = $disk->path($validated['path']);
        $mimeType = File::mimeType($absolutePath) ?: 'application/octet-stream';
        $disposition = ($download ? 'attachment' : 'inline') . '; filename="' . $validated['name'] . '"';

        return response()->file($absolutePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => $disposition,
        ]);
    }
}
