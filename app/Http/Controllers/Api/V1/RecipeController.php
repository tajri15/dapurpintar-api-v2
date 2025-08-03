<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // <-- Import HTTP Client Laravel

class RecipeController extends Controller
{
    // URL dasar dari TheMealDB API
    private const THEMEALDB_API_URL = 'https://www.themealdb.com/api/json/v1/1/';

    /**
     * Mencari resep berdasarkan satu bahan utama.
     */
    public function search(Request $request)
    {
        // Validasi bahwa parameter 'ingredient' ada dan tidak kosong
        $validated = $request->validate([
            'ingredient' => 'required|string|max:255',
        ]);
        
        $ingredient = $validated['ingredient'];

        // Memanggil API eksternal menggunakan HTTP Client Laravel
        $response = Http::withoutVerifying()->get(self::THEMEALDB_API_URL . 'filter.php', [
            'i' => $ingredient,
        ]);

        // Jika panggilan gagal, Laravel akan otomatis melempar error
        $response->throw();

        // Mengembalikan hasil dalam format JSON
        return $response->json();
    }
}