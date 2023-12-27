<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Installment;
use App\Models\Transaction;

class InstallmentController extends Controller
{
    public function store($id): JsonResponse
    {
        $transaction = Transaction::find($id);

        $data = [];
        
        for ($i = 1; $i <= $transaction->installments; $i++) {
            Installment::create([
                'transaction_id' => $id,
                'card_id' => $transaction->card_id,
                'installment_description' => $transaction->transaction_description.' '.$i.'/'.$transaction->installments,
                'installment_value' => $transaction->transaction_value/$transaction->installments,
            ]);
        }
    }
    
    public function show($id): JsonResponse
    {
        try {

            $installments = Installment::where([
                'transaction_id' => $id
            ])->get();

            $response = [];
            foreach($installments  as $installment){
                array_push($response, $installment);
            }

            return response()->json($response, 200);

        } catch(\Exception $e) {

            $errorMessage = "Erro: Esta transação não possui parcelas.";
            $response = [
                "data" => [
                    "message" => $errorMessage,
                    "error" => $e
                ]
            ];
            return response()->json($response, 404);
        }
    }
}
