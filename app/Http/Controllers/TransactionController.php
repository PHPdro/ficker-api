<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Transaction;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Services\TransactionService;

class TransactionController extends Controller
{
    public function store(StoreTransactionRequest $request): JsonResponse
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

    public function update(UpdateTransactionRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['id'] = $request->id;
            $transaction = (new TransactionService())->update($data);

            return response()->json($transaction, 200);

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
