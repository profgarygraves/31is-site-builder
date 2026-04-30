<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Hero-image uploads for Path B (template) sites.
 *
 * Stores files in storage/app/public/sites/{site_id}/ and serves them
 * publicly via /storage/sites/{site_id}/{filename} (Laravel's public disk
 * symlink, set up once with `php artisan storage:link`).
 *
 * Two endpoints:
 *   POST   /sites/{site}/images          → upload one file, return URL
 *   DELETE /sites/{site}/images/{name}   → remove that file from disk
 *
 * Both are policy-gated to the site owner.
 */
class SiteImageController extends Controller
{
    /** Hard cap on file size (server-side). 4 MB. */
    private const MAX_BYTES = 4 * 1024 * 1024;

    /** Allowed MIME types. */
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public function store(Request $request, Site $site): JsonResponse
    {
        Gate::authorize('update', $site);

        $request->validate([
            'image' => ['required', 'file', 'image', 'max:'.intval(self::MAX_BYTES / 1024)],
        ]);

        $file = $request->file('image');

        if (! in_array($file->getMimeType(), self::ALLOWED_MIMES, true)) {
            return response()->json([
                'error' => 'Unsupported image type. Use JPG, PNG, WebP, or GIF.',
            ], 422);
        }

        // Build a stable directory per site, random filename, original extension.
        $ext = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';
        $name = Str::random(16).'.'.strtolower($ext);
        $path = "sites/{$site->id}/{$name}";

        Storage::disk('public')->putFileAs("sites/{$site->id}", $file, $name);

        return response()->json([
            'url' => Storage::disk('public')->url($path),
            'filename' => $name,
            'size_bytes' => $file->getSize(),
        ]);
    }

    public function destroy(Request $request, Site $site, string $filename): JsonResponse
    {
        Gate::authorize('update', $site);

        // Defense: don't accept paths with separators or '..' — only a flat filename.
        if (str_contains($filename, '/') || str_contains($filename, '..') || str_contains($filename, '\\')) {
            abort(400, 'Invalid filename.');
        }

        $path = "sites/{$site->id}/{$filename}";
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        return response()->json(['deleted' => true]);
    }
}
