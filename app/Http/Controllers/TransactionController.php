<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\Installment;
use App\Http\Requests\TransactionRequest;
use App\Services\TransactionService;

class TransactionController extends Controller
{
    public function store(TransactionRequest $request): JsonResponse
    {
        try {
            $transaction = (new TransactionService())->storeTransaction($request->validated());

            return response()->json($transaction, 201);

        } catch(\Exception $e) {
            $response = [
                "data" => [
                    "message" => 'Transação não cadastrada.',
                    "error" => $e->getMessage()
                ]
            ];

            return response()->json($response, 404);
        }
    }

    public function show(Request $request): JsonResponse
    {        
        try {
            $data = $request->toArray();
            $data['id'] = $request->id;
            $transaction = (new TransactionService())->getTransactions($data);

            return response()->json($transaction, 200);

        } catch (\Exception $e) {
            $response = [
                "data" => [
                    "message" => 'Nenhuma transação foi encontrada.',
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
