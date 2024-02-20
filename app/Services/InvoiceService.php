<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Installment;
use App\Models\Transaction;
use App\Models\Card;
use Carbon\Carbon;

class InvoiceService {

    public function createInvoices(Transaction $transaction): void
    {
        $card = Card::findOrFail($transaction->card_id);
        $card_closure = $card->card_closure;
        $card_expiration = $card->card_expiration;
        $current_month= Carbon::now()->format('m');

        if ($current_month == "02" && $card_closure >= 29) {
            $current_invoice_closure = Carbon::now()->endOfMonth()->toDateString();
        }else {
            $current_invoice_closure = Carbon::now()->format('Y-m-'.$card_closure);
        }

        if ($transaction->date > $current_invoice_closure) {
            $invoice_closure = Carbon::parse($current_invoice_closure)->addMonth()->format('Y-m-'.$card_closure);
            $invoice_expiration = Carbon::parse($invoice_closure)->addMonth()->format('Y-m-'.$card_expiration);
        }else {
            $invoice_closure = $current_invoice_closure;
            $invoice_expiration = Carbon::parse($invoice_closure)->addMonth()->format('Y-m-'.$card_expiration);
        }

        for ($i=1; $i <= $transaction->installments; $i++) {

            if (Carbon::parse($invoice_expiration)->format('l') == 'Saturday') {
                $invoice_expiration = Carbon::parse($invoice_expiration)->addDays(2)->toDateString();
            }elseif (Carbon::parse($invoice_expiration)->format('l') == 'Sunday') {
                $invoice_expiration = Carbon::parse($invoice_expiration)->addDays(1)->toDateString();
            }

            $invoice = Invoice::create([
                'card_id' => $transaction->card_id,
                'invoice_closure' => $invoice_closure,
                'invoice_expiration' => $invoice_expiration
            ]);
            
            Installment::create([
                'transaction_id' => $transaction->id,
                'installment_description' => $transaction->transaction_description.' '.$i.'/'.$transaction->installments,
                'installment_value' => $transaction->transaction_value / $transaction->installments,
                'invoice_id' => $invoice->id
            ]);

            $invoice_closure = Carbon::parse($invoice_closure)->addMonth()->toDateString();
            $invoice_expiration = Carbon::parse($invoice_expiration)->addMonth()->format('Y-m-'.$card_expiration);
        }
    }
}
