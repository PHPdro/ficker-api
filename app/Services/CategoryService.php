<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class CategoryService {

    public function storeCategory(array $data): Category
    {
        if(count($data) > 2) {

            $category = Category::create([
                'user_id' => Auth::id(),
                'category_description' => $data['category_description'],
                'type_id' => $data['type_id']
            ]);

        } else {
            $category = Category::create($data);
        }
        return $category;
    }
}