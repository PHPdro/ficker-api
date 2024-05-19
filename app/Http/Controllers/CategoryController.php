<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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

    public function show(Request $request): JsonResponse
    {
        try {
            $data = $request->toArray();
            $data['id'] = $request->id;
            $categories = (new CategoryService())->getCategories($data);

            return response()->json($categories, 200);

        } catch (\Exception $e) {
            $errorMessage = "Nenhuma categoria foi encontrada.";
            $response = [
                "message" => $errorMessage,
                "error" => $e->getMessage()
            ];

            return response()->json($response, 404);
        }
    }
}
