<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Card;
use App\Models\Category;
use App\Models\Installment;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Auth;

class TransactionService {

    public function storeTransaction(array $data): array
    {
        $data['user_id'] = Auth::id();

        if ($data['category_id'] == 0) {
            $category = (new CategoryService())->storeCategory($data);
            $data['category_id'] = $category->id;
        }

        $transaction = Transaction::create($data);
        
        if($transaction->payment_method_id == 4) {
            Card::findOrFail($transaction->card_id);
            (new InvoiceService())->createInvoices($transaction);
        }

        $installments = Installment::where('transaction_id', $transaction->id)->get();

        $response = [
            'data' => [
                'trasanction' => $transaction,
                'installments' => $installments
            ]
        ];

        return $response;
    }

    public function getTransactions(array $data): array
    {
        
        if($data['id'] == 0) {

            $transactions = Transaction::query();

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
    
            // $most_expensive_transaction = Transaction::orderBy('transaction_value', 'desc')
            //     ->where([
            //         'user_id'=> Auth::id(),
            //         'type_id' => 2])
            //     ->first()->transaction_value;
    
            // $total_incomes = Transaction::where([
            //     'user_id' => Auth::id(),
            //     'type_id' => 1
            // ])->get();
    
            // $total_spendings = Transaction::where([
            //     'user_id' => Auth::id(),
            //     'type_id' => 2
            // ])->get();
    
            $response = [
                'data' => [
                    // 'total_incomes' => count($total_incomes),
                    // 'total_spendings' => count($total_spendings),
                    // 'most_expensive_transaction' => $most_expensive_transaction,
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

    public function update(array $data): array
    {
        Transaction::find($data['id'])->update($data);

        $transaction = Transaction::find($data['id']);

            if($transaction->payment_method_id == 4) {

                if(array_key_exists('installments', $data)) {

                    $installments = Installment::where('transaction_id', $data['id'])->get();
                    $transaction = Transaction::find($data['id']);

                    Installment::where('transaction_id', $data['id'])->delete();
                    $date = $transaction->date;
                    for ($i = 1; $i <= $data['installments']; $i++) {

                        Installment::create([
                            'transaction_id' => $data['id'],
                            'installment_description' => $transaction->transaction_description.' '.$i.'/'.$data['installments'],
                            'installment_value' => $transaction->transaction_value / $data['installments'],
                            'card_id' => $transaction->card_id,
                            'pay_day' => $date
                        ]);

                        $date = strtotime('+1 months', strtotime($date));
                        $date = date('Y-m-d', $date);
                    }
                }

                if(array_key_exists('transaction_value', $data)) {

                    Installment::where('transaction_id', $data['id'])->get()->each(function($installment) use ($data) {
                        
                        $transaction = Transaction::find($data['id']);
    
                        $installment->update([
                            'installment_value' => $data['transaction_value'] / $transaction->installments
                        ]);
                    });
                }

                if(array_key_exists('transaction_description', $data)) {

                    $count = 1;
                    Installment::where('transaction_id', $data['id'])->get()->each(function($installment) use ($data, &$count){

                        $transaction = Transaction::find($data['id']);
    
                        $installment->update([
                            'installment_description' => $data['transaction_description'].' '.$count.'/'.$transaction->installments,
                        ]);

                        $count++;
                    });
                }

                if(array_key_exists('date', $data)) {

                    $date = $data['date'];
                    Installment::where('transaction_id', $data['id'])->get()->each(function($installment) use (&$date){
    
                        $installment->update([
                            'pay_day' => $date,
                        ]);

                        $date = strtotime('+1 months', strtotime($date));
                        $date = date('Y-m-d', $date);
                    });
                }
            }

            $installments = Installment::where('transaction_id', $data['id'])->get();

            $response = [
                "transaction" => $transaction,
                "installments" => $installments
            ];

        return $response;
    }
}
