<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use App\Services\CategoryService;
use App\Http\Requests\CategoryRequest;

class CategoryController extends Controller
{

    public function store(CategoryRequest $request): JsonResponse
    {
        try {
            $category = (new CategoryService())->storeCategory($request->validated());

            return response()->json($category, 201);

        } catch (\Exception $e) {
            $errorMessage = "Os dados informados são inválidos.";
            $response = [
                'message' => $errorMessage,
                'errors' => $e->getMessage()
            ];

            return response()->json($response, 404);
        }
    }

    public function showCategories(): JsonResponse
    {
        try {

            $categories = [];

            foreach (Auth::user()->categories as $category) {

                $category_spending = Transaction::whereMonth('date', now()->month)
                    ->where('category_id', $category->id)
                    ->where('type_id', 2)
                    ->sum('transaction_value');

                $category->category_spending = $category_spending;
                array_push($categories, $category);
            }

            $response = [
                'data' => [
                    'categories' => $categories
                ]
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $errorMessage = "Nenhuma categoria foi encontrada";
            $response = [
                "data" => [
                    "message" => $errorMessage,
                    "error" => $e->getMessage()
                ]
            ];

            return response()->json($response, 404);
        }
    }

    public function showCategoriesByType($id): JsonResponse
    {
        try {
            $categories = Category::where([
                'user_id' => Auth::user()->id,
                'type_id' => $id
            ])->get();

            $response = [];
            foreach ($categories as $category) {
                array_push($response, $category);
            }

            return response()->json($response, 200);
        } catch (\Exception $e) {

            $errorMessage = "Nenhuma categoria encontrada.";
            $response = [
                "data" => [
                    "message" => $errorMessage,
                    "error" => $e->getMessage()
                ]
            ];

            return response()->json($response, 404);
        }
    }

    public static function showCategory($id): JsonResponse
    {
        try {

            $category = Category::find($id);

            $description = $category->category_description;

            $response = [
                'data' => [
                    'category_description' => $description
                ]
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            $errorMessage = "Error: " . $e;
            $response = [
                "data" => [
                    "message" => $errorMessage,
                    "error" => $e->getMessage()
                ]
            ];

            return response()->json($response, 404);
        }
    }
}
