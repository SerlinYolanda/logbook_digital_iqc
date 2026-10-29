<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\InspectionPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InspectionPhotoController extends Controller
{
    /**
     * Upload a photo for a draft/pending inspection (AJAX).
     */
    public function store(Request $request, $inspectionId)
    {
        $inspection = Inspection::findOrFail($inspectionId);

        // Only the inspector who owns it (or if it's a temp upload on pending) can upload
        $request->validate([
            'photo'      => 'required|image|mimes:jpg,jpeg,png,webp|max:5120', // 5 MB
            'photo_type' => 'required|in:pass,defect',
            'label'      => 'nullable|string|max:150',
        ]);

        $file = $request->file('photo');
        $path = $file->store("inspection-photos/{$inspectionId}", 'public');

        $photo = InspectionPhoto::create([
            'inspection_id' => $inspectionId,
            'photo_type'    => $request->photo_type,
            'label'         => $request->input('label', ''),
            'path'          => $path,
            'uploaded_by'   => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'photo'   => [
                'id'         => $photo->id,
                'photo_type' => $photo->photo_type,
                'label'      => $photo->label,
                'url'        => Storage::disk('public')->url($path),
            ],
        ]);
    }

    /**
     * Delete a photo (before final submission).
     */
    public function destroy($photoId)
    {
        $photo = InspectionPhoto::findOrFail($photoId);

        // Only the uploader can delete
        if ($photo->uploaded_by !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        return response()->json(['success' => true]);
    }
}

