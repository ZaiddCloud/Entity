<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookChild;
use Illuminate\Http\Request;

class BookEditorController extends Controller
{
    /**
     * Save the content of a book child and set protection flag.
     */
    public function save(Request $request, BookChild $child)
    {
        $request->validate([
            'content_blocks' => 'required|array',
        ]);

        // Create version from OLD content before updating
        $child->createVersion('Manual update via Editor');

        $child->update([
            'content_blocks' => $request->content_blocks,
            'is_manually_edited' => true,
            'last_updated' => now()->toDateTimeString(),
        ]);

        return response()->json([
            'message' => 'Content saved successfully',
            'child' => $child
        ]);
    }

    /**
     * Restore a specific version of the content.
     */
    public function restore(Request $request, BookChild $child, $versionIndex)
    {
        $versions = $child->versions ?? [];

        if (!isset($versions[$versionIndex])) {
            return response()->json(['error' => 'Version not found'], 404);
        }

        $targetVersion = $versions[$versionIndex];

        // Create a snapshot of CURRENT content before reverting
        $child->createVersion('Snapshot before restoring version ' . $versionIndex);

        $child->update([
            'content_blocks' => $targetVersion['content_blocks'],
            'last_updated' => now()->toDateTimeString(),
        ]);

        return response()->json([
            'message' => 'Content restored successfully',
            'child' => $child
        ]);
    }
}
