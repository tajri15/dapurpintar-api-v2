<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PantryItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PantryController extends Controller
{
    /**
     * Menampilkan daftar semua bahan di pantry milik pengguna yang sedang login.
     */
    public function index(Request $request)
    {
        return $request->user()->pantryItems()->get();
    }

    /**
     * Menyimpan bahan baru ke dalam pantry milik pengguna yang sedang login.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|numeric',
            'unit' => 'required|string|max:50',
        ]);

        $pantryItem = $request->user()->pantryItems()->create($validated);

        return response($pantryItem, Response::HTTP_CREATED);
    }

    /**
     * Menampilkan satu bahan spesifik.
     */
    public function show(Request $request, PantryItem $pantryItem)
    {
        // Memastikan item ini milik user yang sedang login
        if ($request->user()->id !== $pantryItem->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return $pantryItem;
    }

    /**
     * Mengubah data bahan yang sudah ada.
     */
    public function update(Request $request, PantryItem $pantryItem)
    {
        // Memastikan item ini milik user yang sedang login
        if ($request->user()->id !== $pantryItem->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'quantity' => 'sometimes|required|numeric',
            'unit' => 'sometimes|required|string|max:50',
        ]);

        $pantryItem->update($validated);

        return $pantryItem;
    }

    /**
     * Menghapus bahan dari pantry.
     */
    public function destroy(Request $request, PantryItem $pantryItem)
    {
        // Memastikan item ini milik user yang sedang login
        if ($request->user()->id !== $pantryItem->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $pantryItem->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}