<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class CategoryService {

    public function storeCategory(array $data): Category
    {
        $category = Category::create($data);

        return $category;
    }

    public function newCategory(string $description, int $type): Category
    {
        try {
            $category = Category::create([
                'user_id' => Auth::id(),
                'category_description' => $description,
                'type_id' => $type
            ]);
            
            return $category;

        } catch (\Exception $e) {
            $errorMessage = "A categoria não foi criada.";
            $response = [
                'Error' => [
                    'message' => $errorMessage,
                    'error' => $e->getMessage()
                ]
            ];

            return response()->json($response, 404);
        }
    }
}