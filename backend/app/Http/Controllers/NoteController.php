<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get notes for logged-in user
        $notes = Note::where('user_id', $request->user()->id)->get();
        return response()->json(['notes' => $notes]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'title' => 'required|string|max:255',
            'text'  => 'required|string|max:1000',
        ]);

        // Create note
        try {
            $note = Note::create([
                'user_id' => $request->user()->id,
                'title'   => $request->title,
                'text'    => $request->text,
            ]);

            return response()->json(['note' => $note], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create note',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Note $note)
    {
        if ($note->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(['note' => $note]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Note $note)
    {
        if ($note->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'text'  => 'required|string|max:1000',
        ]);

        try {
            $note->update([
                'title' => $request->title,
                'text'  => $request->text,
            ]);

            return response()->json(['note' => $note]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update note',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note)
    {
        if ($note->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $note->delete();
            return response()->json(['message' => 'Note deleted']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete note',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
