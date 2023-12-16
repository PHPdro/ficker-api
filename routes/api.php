<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SpendingController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\PaymentController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('transactions')->group(function () {
        Route::post('/', [TransactionController::class, 'store']);
        Route::get('/{id}', [TransactionController::class, 'show']);
        Route::put('/{id}', [TransactionController::class, 'update']);
        Route::delete('/{id}', [TransactionController::class, 'destroy']);
        // Route::get('/{id}', [TransactionController::class, 'showTransaction']);
        Route::get('/{id}/installments', [InstallmentController::class, 'showInstallments']); // Parcelas de uma transação
    });

    //Rotas das categorias
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories', [CategoryController::class, 'showCategories']);
    Route::get('/categories/{id}', [CategoryController::class, 'showCategory']);
    Route::get('/categories/type/{id}', [CategoryController::class, 'showCategoriesByType']); // Categorias de entrada (1), saída (2) ou cartão de crédito (3)

    //Rotas dos cartões
    Route::post('/cards', [CardController::class, 'store']);
    Route::get('/cards', [CardController::class, 'showCards']);
    Route::get('/cards/{id}/invoice', [CardController::class, 'showCardInvoice']);
    Route::get('/cards/{id}/installments', [CardController::class, 'showInvoiceInstallments']); // Transações de um cartão no mês atual
    Route::get('/flags', [CardController::class, 'showFlags']);

    //Rotas dos gastos
    Route::get('/spendings', [SpendingController::class, 'spendings']); // Saídas por ano, mês ou dia
    Route::get('/spending', [SpendingController::class, 'showSpending']);
    Route::post('/spendings', [SpendingController::class, 'store']);
    Route::put('/spending/update/{id}', [SpendingController::class, 'update']);

    //Rotas dos saldos
    Route::get('/balance', [BalanceController::class, 'balance']); //Mostra o saldo atual;

    //Rotas dos métodos de pagamento (id e descrição)
    Route::get('/payment-methods', [PaymentController::class, 'showPaymentMethods']);
});

require __DIR__ . '/auth.php';
