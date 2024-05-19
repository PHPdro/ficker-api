<?php

namespace App\Services;

use App\Models\Card;
use Illuminate\Support\Facades\Auth;

class CardService {

    public function createCard(array $data): array
    {
        $data['user_id'] = Auth::id();

        $card = Card::create($data);

        $response = [
            'data' => [
                'card' => $card
            ]
        ];

        return $response;
    }

    public function getCards(array $data): array
    {
        if($data['id'] == 0) {
            $cards = Auth::user()->cards;
        } else {
            $cards = Card::findOrFail($data['id']);
        }
        
        $response = [
            'data' => [
                'cards' => $cards
            ]
        ];

        return $response;
    }
}
