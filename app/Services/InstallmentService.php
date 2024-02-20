<?php

namespace App\Services;

use App\Models\Installment;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

class InstallmentService {
    
    public function storeInstallments(Transaction $transaction): Collection
    {
        $date = $transaction->date;
            for ($i = 1; $i <= $transaction->installments; $i++) {

                Installment::create([
                    'transaction_id' => $transaction->id,
                    'installment_description' => $transaction->transaction_description.' '.$i.'/'.$transaction->installments,
                    'installment_value' => $transaction->transaction_value / $transaction->installments,
                ]);

                $date = strtotime('+1 months', strtotime($date));
                $date = date('Y-m-d', $date);
            }
        $installments = Installment::where('transaction_id', $transaction->id)->get();

        return $installments;
    }
}
