<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class CategoryService {

    public function storeCategory(array $data) 
    {
        if(count($data) > 2) { # For categories created along with transactions

            $category = Category::create([
                'user_id' => Auth::id(),
                'category_description' => $data['category_description'],
                'type_id' => $data['type_id']
            ]);

            return $category;

        } else {
            $category = Category::create($data);

            $response = [
                'data' => [
                    'category' => $category
                ]
            ];
            
            return $response;
        }
    }

    public function getCategories(array $data): array
    {
        if($data['id'] == 0) {

            $categories = Category::query();

            if (array_key_exists('type', $data)) {
                $categories->where([
                                'user_id' => Auth::id(), 
                                'type_id' => $data['type']]);
            }

            $result = $categories->get();

            foreach ($result as $category) {

                $category_expenses = Transaction::whereMonth('date', now()->month)
                    ->where('category_id', $category->id)
                    ->sum('transaction_value');

                $category['current_category_expenses'] = $category_expenses;
            }

            $response = [
                'data' => [
                    'categories' => $result
                ]
            ];

        } else {

            $category = Category::findOrFail($data['id']);
            $category_expenses = Transaction::whereMonth('date', now()->month)
                    ->where('category_id', $category->id)
                    ->sum('transaction_value');

            $category->current_category_expenses = $category_expenses;

            $response = [
                'data' => [
                    'category' => $category
                ]
            ];
        }
        
        return $response;
    }
}