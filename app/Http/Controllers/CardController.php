<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\CardRequest;
use App\Services\CardService;

class CardController extends Controller
{
    public function store(CardRequest $request): JsonResponse
    {
        try {
            $card = (new CardService())->createCard($request->validated());

            return response()->json($card, 201);

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
            $cards = (new CardService())->getCards($data);

            return response()->json($cards, 200);

        } catch (\Exception $e) {
            $errorMessage = "Nenhum cartão foi encontrado.";
            $response = [
                "message" => $errorMessage,
                "error" => $e->getMessage()
            ];

            return response()->json($response, 404);
        }
    }
}
