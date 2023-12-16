<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Auth;

class TransactionService {

    public function storeTransaction(array $data): array
    {
        $data['user_id'] = Auth::id();

        if ($data['category_id'] == 0) {
            $category = (new CategoryService())->newCategory($data['category_description'], $data['type_id']);
            $data['category_id'] = $category->id;
        }

        $transaction = Transaction::create($data);

        $response = [
            'data' => [
                'trasanction' => $transaction
            ]
        ];

        return $response;
    }

    public function getTransactions(array $data): array
    {
        $transactions = Transaction::query();

        if($data['id'] == 0) {

            if (array_key_exists('type', $data)) {
                $transactions->where([
                                'user_id' => Auth::id(), 
                                'type_id' => $data['type']])
                                ->orderBy('date', 'desc');
            }
    
            if (array_key_exists('category', $data)) {
                $transactions->where([
                                'user_id' => Auth::id(), 
                                'category_id' => $data['category']])
                                ->orderBy('date', 'desc');
            }
    
            if (array_key_exists('card', $data)) {
                $transactions->where([
                                'user_id' => Auth::id(), 
                                'card_id' => $data['card']])
                                ->orderBy('date', 'desc');
            }
    
            if (array_key_exists('payment-method', $data)) {
                $transactions->where([
                                'user_id' => Auth::id(), 
                                'payment_method_id' => $data['payment-method']])
                                ->orderBy('date', 'desc');
            }
    
            $result = $transactions->get();
    
            foreach ($result as $transaction) {
    
                $description = Category::find($transaction->category_id)->category_description;
                $transaction->category_description = $description;
            }
    
            $most_expensive_transaction = Transaction::orderBy('transaction_value', 'desc')
                ->where([
                    'user_id'=> Auth::id(),
                    'type_id' => 2])
                ->first()->transaction_value;
    
            $total_incomes = Transaction::where([
                'user_id' => Auth::id(),
                'type_id' => 1
            ])->get();
    
            $total_spendings = Transaction::where([
                'user_id' => Auth::id(),
                'type_id' => 2
            ])->get();
    
            $response = [
                'data' => [
                    'total_incomes' => count($total_incomes),
                    'total_spendings' => count($total_spendings),
                    'most_expensive_transaction' => $most_expensive_transaction,
                    'transactions' => $result
                ]
            ];
        } else {

            $transaction = Transaction::findOrFail($data['id']);
    
            $response = [
                'data' => [
                    'transaction' => $transaction
                ]
            ];
        }

        return $response;
    }
}
