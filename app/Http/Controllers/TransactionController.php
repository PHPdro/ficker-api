<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Installment;
use App\Http\Requests\TransactionRequest;
use App\Services\TransactionService;

class TransactionController extends Controller
{
    public function store(TransactionRequest $request): JsonResponse
    {
        try {

            $validated = $request->validated();

            $validated['user_id'] = Auth::id();

            // Cadastrando nova categoria

            if ($request->category_id == 0) {

                $category = CategoryController::storeInTransaction($request->category_description, $request->type_id);
                $validated['category_id'] = $category->id;
            }

            // Cadastrando transação

            $transaction = Transaction::create($validated);

            if (!is_null($request->installments)) {         

                // Chamar método de cadastro de parcelas/fatura
            }

            $response = [
                'data' => [
                    'trasanction' => $transaction
                ]
            ];

            return response()->json($response, 201);

        } catch(\Exception $e) {
            $errorMessage = 'Transação não cadastrada.';
            $response = [
                "data" => [
                    "message" => $errorMessage,
                    "error" => $e
                ]
            ];

            return response()->json($response, 404);
        }
    }

    public function showTransactions(Request $request): JsonResponse
    {
        try {

            $transactions = Transaction::query();

            if ($request->has('type')) {
                $transactions->where([
                                'user_id' => Auth::id(), 
                                'type_id' => $request->query('type')])
                             ->orderBy('date', 'desc');
            }

            if ($request->has('category')) {
                $transactions->where([
                                'user_id' => Auth::id(), 
                                'category_id' => $request->query('category')])
                             ->orderBy('date', 'desc');
            }

            if ($request->has('card')) {
                $transactions->where([
                                'user_id' => Auth::id(), 
                                'card_id' => $request->query('card')])
                             ->orderBy('date', 'desc');
            }

            if ($request->has('payment-method')) {
                $transactions->where([
                                'user_id' => Auth::id(), 
                                'payment_method_id' => $request->query('pqyment-method')])
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

            return response()->json($response, 200);

        } catch (\Exception $e) {
            $errorMessage = 'Nenhuma transação foi encontrada.';
            $response = [
                "data" => [
                    "message" => $errorMessage,
                    "error" => $e->getMessage()
                ]
            ];

            return response()->json($response, 404);
        }
    }

    public function showTransaction($id): JsonResponse
    {
        try {

            $transaction = Transaction::find($id);

            $description = Category::find($transaction->category_id)->category_description;
            $transaction->category_description = $description;


            $response = [
                'data' => [
                    'transaction' => $transaction
                ]
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $errorMessage = "Erro: Transação não encontrada.";
            $response = [
                "data" => [
                    "message" => $errorMessage,
                    "error" => $e->getMessage()
                ]
            ];
            return response()->json($response, 404);
        }
    }

    public function update(Request $request): JsonResponse
    {
        try {

            $request->validate([
                'transaction_description' => ['string', 'max:50'],
                'date' => ['date'],
                'transaction_value' => ['decimal:0,2', 'min:1'],
                'payment_method_id' => ['min:1', 'max:4'],
                'installments' => ['min:1'],
            ]);

            Transaction::find($request->id)->update($request->only([
                'transaction_description',
                'category_id',
                'date',
                'transaction_value',
                'payment_method_id',
                'installments'
            ]));

            $transaction = Transaction::find($request->id);

            if($transaction->payment_method_id == 4) {

                if(!(is_null($request->installments))) {

                    $installments = Installment::where('transaction_id', $request->id)->get();
                    $transaction = Transaction::find($request->id);

                    Installment::where('transaction_id', $request->id)->delete();
                    $date = $transaction->date;
                    for ($i = 1; $i <= $request->installments; $i++) {

                        Installment::create([
                            'transaction_id' => $request->id,
                            'installment_description' => $transaction->transaction_description.' '.$i.'/'.$request->installments,
                            'installment_value' => $transaction->transaction_value / $request->installments,
                            'card_id' => $transaction->card_id,
                            'pay_day' => $date
                        ]);

                        $date = strtotime('+1 months', strtotime($date));
                        $date = date('Y-m-d', $date);
                    }
                }

                if(!(is_null($request->transaction_value))) {

                    Installment::where('transaction_id', $request->id)->get()->each(function($installment) use ($request) {
                        
                        $transaction = Transaction::find($request->id);
    
                        $installment->update([
                            'installment_value' => $request->transaction_value / $transaction->installments
                        ]);
                    });
                }

                if(!(is_null($request->transaction_description))) {

                    $count = 1;
                    Installment::where('transaction_id', $request->id)->get()->each(function($installment) use ($request, &$count){

                        $transaction = Transaction::find($request->id);
    
                        $installment->update([
                            'installment_description' => $request->transaction_description.' '.$count.'/'.$transaction->installments,
                        ]);

                        $count++;
                    });
                }

                if(!(is_null($request->date))) {

                    $date = $request->date;
                    Installment::where('transaction_id', $request->id)->get()->each(function($installment) use (&$date){
    
                        $installment->update([
                            'pay_day' => $date,
                        ]);

                        $date = strtotime('+1 months', strtotime($date));
                        $date = date('Y-m-d', $date);
                    });
                }
            }

            $installments = Installment::where('transaction_id', $request->id)->get();

            $response = [
                "transaction" => $transaction,
                "installments" => $installments
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {

            $errorMessage = $e->getMessage();
            $response = [
                "data" => [
                    "message" => $errorMessage,
                ]
            ];
            return response()->json($response, 400);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            Transaction::findOrFail($id)->delete();

            $message = 'Transação excluída com sucesso.';

            $response = [
                'data' => [
                    'message' => $message
                ]
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {

            $errorMessage = "Erro: Esta transação não existe.";
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
