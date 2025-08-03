<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class RecipeFinderController extends Controller
{
    private const THEMEALDB_API_URL = 'https://www.themealdb.com/api/json/v1/1/';

    public function findRecipesByPantry(Request $request)
    {
        try {
            $user = $request->user();
            $pantryItems = $user->pantryItems()->get();
            
            if ($pantryItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your pantry is empty. Add some ingredients first.'
                ], 400);
            }

            $ourIngredients = $pantryItems->pluck('name')
                ->map(fn($name) => Str::lower(trim($name)))
                ->filter()
                ->unique()
                ->values();

            Log::info("Searching recipes for ingredients: " . json_encode($ourIngredients));

            $recipeScores = [];
            $processedRecipes = 0;

            foreach ($ourIngredients as $ingredient) {
                try {
                    $response = Http::withoutVerifying()
                        ->timeout(10)
                        ->get(self::THEMEALDB_API_URL . 'filter.php', ['i' => $ingredient]);

                    if ($response->failed()) {
                        Log::warning("API request failed for ingredient: $ingredient", [
                            'status' => $response->status(),
                            'response' => $response->body()
                        ]);
                        continue;
                    }

                    $recipes = $response->json()['meals'] ?? [];
                    $processedRecipes += count($recipes);

                    foreach ($recipes as $recipe) {
                        if (empty($recipe['idMeal'])) continue;

                        $recipeId = $recipe['idMeal'];
                        if (!isset($recipeScores[$recipeId])) {
                            $recipeScores[$recipeId] = [
                                'recipe' => $recipe,
                                'score' => 0,
                                'matchedIngredients' => []
                            ];
                        }
                        $recipeScores[$recipeId]['score']++;
                        $recipeScores[$recipeId]['matchedIngredients'][] = $ingredient;
                    }
                } catch (\Exception $e) {
                    Log::error("Error processing ingredient $ingredient: " . $e->getMessage());
                    continue;
                }
            }

            if (empty($recipeScores)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No recipes found for your ingredients. Try adding more common ingredients.'
                ], 404);
            }

            // Sort by score descending
            usort($recipeScores, fn($a, $b) => $b['score'] <=> $a['score']);

            // Get top 10 candidates
            $topCandidates = array_slice($recipeScores, 0, 10);
            $finalMatchedRecipes = [];

            foreach ($topCandidates as $candidate) {
                try {
                    $recipeId = $candidate['recipe']['idMeal'];
                    $detailResponse = Http::withoutVerifying()
                        ->timeout(10)
                        ->get(self::THEMEALDB_API_URL . 'lookup.php', ['i' => $recipeId]);

                    $recipeDetails = ($detailResponse->json()['meals'] ?? [null])[0];
                    if (!$recipeDetails) continue;

                    $requiredIngredients = $this->getIngredientsFromRecipe($recipeDetails);
                    if (empty($requiredIngredients)) continue;

                    $haveCount = count(array_intersect($ourIngredients->toArray(), $requiredIngredients));
                    $matchPercentage = ($haveCount / count($requiredIngredients)) * 100;

                    // Lower threshold to 50% for better results
                    if ($matchPercentage >= 50) {
                        $finalRecipe = $candidate['recipe'];
                        $finalRecipe['matchPercentage'] = round($matchPercentage);
                        $finalRecipe['missingCount'] = count($requiredIngredients) - $haveCount;
                        $finalRecipe['matchedIngredients'] = $candidate['matchedIngredients'];
                        $finalMatchedRecipes[] = $finalRecipe;
                    }
                } catch (\Exception $e) {
                    Log::error("Error processing recipe details for $recipeId: " . $e->getMessage());
                    continue;
                }
            }

            if (empty($finalMatchedRecipes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Found recipes but none matched enough ingredients. Try adding more ingredients.'
                ], 404);
            }

            // Sort by match percentage
            usort($finalMatchedRecipes, fn($a, $b) => $b['matchPercentage'] <=> $a['matchPercentage']);

            return response()->json([
                'success' => true,
                'data' => $finalMatchedRecipes,
                'meta' => [
                    'total_ingredients' => $ourIngredients->count(),
                    'processed_recipes' => $processedRecipes,
                    'matched_recipes' => count($finalMatchedRecipes)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Error in findRecipesByPantry: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching for recipes.'
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $response = Http::withoutVerifying()
                ->get('https://www.themealdb.com/api/json/v1/1/lookup.php', ['i' => $id]);
            
            $meal = $response->json()['meals'][0] ?? null;
            
            if (!$meal) {
                return response()->json(['message' => 'Recipe not found'], 404);
            }
            
            return response()->json($meal);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch recipe'], 500);
        }
    }

    private function getIngredientsFromRecipe(array $recipeDetails): array
    {
        $ingredients = [];
        for ($i = 1; $i <= 20; $i++) {
            $ingredientKey = 'strIngredient' . $i;
            if (!empty($recipeDetails[$ingredientKey])) {
                $ingredient = Str::lower(trim($recipeDetails[$ingredientKey]));
                $ingredient = preg_replace('/\s+/', ' ', $ingredient);
                if (!empty($ingredient)) {
                    $ingredients[] = $ingredient;
                }
            }
        }
        return array_unique($ingredients);
    }
}