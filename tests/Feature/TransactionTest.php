<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Installment;
use App\Models\User;
use App\Models\Type;
use App\Models\PaymentMethod;
use App\Models\Card;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        Auth::login($user);
        Type::factory()->count(2)->create();
        Category::factory()->create();
        PaymentMethod::factory()->count(4)->create();
        Card::factory()->create();
    }

    public function test_users_can_store_incomes(): void
    {
        $response = $this->post('/api/transactions',[
            'category_id' => 1,
            'type_id' => 1,
            'transaction_description' => 'CURSO DE LARAVEL',
            'transaction_value' => 500.00,
            'date' => date('Y-m-d')
        ]);

        $response->assertStatus(201);
    }

    public function test_users_can_store_expenses(): void
    {
        $response = $this->post('/api/transactions',[
            'category_id' => 1,
            'type_id' => 2,
            'payment_method_id' => 1,
            'transaction_description' => 'CURSO DE LARAVEL',
            'transaction_value' => 500.00,
            'date' => date('Y-m-d'),
        ]);

        $response->assertStatus(201);
    }

    public function test_users_can_store_credit_card_expenses(): void
    {
        $response = $this->post('/api/transactions',[
            'type_id' => 2,
            'payment_method_id' => 4,
            'category_id' => 1,
            'card_id' => 1,
            'transaction_description' => 'Mc Donalds',
            'transaction_value' => 50.99,
            'date' => date('Y-m-d'),
            'installments' => 2
        ]);

        $response->assertStatus(201);
    }

    public function test_users_can_store_transactions_with_a_new_category(): void
    {
        $response = $this->post('/api/transactions',[
            'category_id' => 0,
            'category_description' => 'Comida',
            'type_id' => 1,
            'transaction_description' => 'Mc Donalds',
            'transaction_value' => 50.99,
            'date' => date('Y-m-d'),
        ]);

        $response->assertStatus(201);
    }

    public function test_users_can_not_store_transactions_without_a_category(): void
    {
        $response = $this->post('/api/transactions',[
            'type_id' => 1,
            'transaction_description' => 'Mc Donalds',
            'transaction_value' => 50.99,
            'date' => date('Y-m-d'),
        ]);

        $error = session('errors')->get('category_id')[0];
        $this->assertEquals($error,'The category field is required.');
    }

    public function test_users_can_not_store_transactions_without_the_new_category_description(): void
    {
        $this->post('/api/transactions',[
            'category_id' => 0,
            'type_id' => 2,
            'transaction_description' => 'Mc Donalds',
            'transaction_value' => 50.99,
            'date' => date('Y-m-d'),
        ]);

        $errors = session('errors')->get('category_description')[0];
        $this->assertEquals($errors,'The category description field is required.');
    }

    public function test_users_can_not_store_a_credit_card_transaction_without_a_type(): void
    {
        $this->post('/api/transactions',[
            'category_id' => 1,
            'payment_method_id' => 4,
            'card_id' => 1,
            'transaction_description' => 'CURSO DE LARAVEL',
            'transaction_value' => 500.00,
            'date' => date('Y-m-d'),
            'installments' => 2
        ]);

        $errors = session('errors')->get('type_id')[0];
        $this->assertEquals($errors,'The type field is required.');
    }

    public function test_users_can_not_store_a_credit_card_transaction_without_a_description(): void
    {
        
        
        $this->post('/api/transactions',[
            'category_id' => 1,
            'type_id' => 2,
            'payment_method_id' => 4,
            'card_id' => 1,
            'transaction_value' => 500.00,
            'date' => date('Y-m-d'),
            'installments' => 2
        ]);

        $errors = session('errors');
        $this->assertEquals($errors->get('transaction_description')[0],"O campo descrição é obrigatório.");
        $this->assertEquals(0, count(Transaction::all()));
        $this->assertEquals(0, count(Installment::all()));
    }

    public function test_users_can_not_store_a_credit_card_transaction_without_a_value(): void
    {
        
        
        $this->post('/api/transactions',[
            'category_id' => 1,
            'type_id' => 2,
            'payment_method_id' => 4,
            'card_id' => 1,
            'transaction_description' => 'CURSO DE LARAVEL',
            'date' => date('Y-m-d'),
            'installments' => 2
        ]);

        $errors = session('errors');
        $this->assertEquals($errors->get('transaction_value')[0],"Informe o valor da transação.");
        $this->assertEquals(0, count(Transaction::all()));
        $this->assertEquals(0, count(Installment::all()));
    }

    public function test_users_can_not_store_a_credit_card_transaction_without_a_date(): void
    {
        
        
        $this->post('/api/transactions',[
            'category_id' => 1,
            'type_id' => 2,
            'payment_method_id' => 4,
            'card_id' => 1,
            'transaction_description' => 'CURSO DE LARAVEL',
            'transaction_value' => 500.00,
            'installments' => 2        
        ]);

        $errors = session('errors');
        $this->assertEquals($errors->get('date')[0],"O campo data é obrigatório.");
        $this->assertEquals(0, count(Transaction::all()));
        $this->assertEquals(0, count(Installment::all()));
    }

    public function test_users_can_not_store_a_credit_card_transaction_without_a_payment_method(): void
    {
        
        
        
        $this->post('/api/transactions',[
            'category_id' => 1,
            'type_id' => 2,
            'card_id' => 1,
            'transaction_description' => 'CURSO DE LARAVEL',
            'transaction_value' => 500.00,
            'date' => date('Y-m-d'),
            'installments' => 2
        ]);

        $errors = session('errors');
        $this->assertEquals($errors->get('payment_method_id')[0],"É necessário informar um método de pagamento para esse tipo de transação.");
        $this->assertEquals(0, count(Transaction::all()));
        $this->assertEquals(0, count(Installment::all()));
    }

    public function test_users_can_not_store_a_credit_card_transaction_without_a_card(): void
    {
        
        
        $this->post('/api/transactions',[
            'category_id' => 1,
            'type_id' => 2,
            'payment_method_id' => 4,
            'transaction_description' => 'CURSO DE LARAVEL',
            'transaction_value' => 500.00,
            'date' => date('Y-m-d'),
            'installments' => 2
        ]);

        $errors = session('errors');
        $this->assertEquals($errors->get('card_id')[0],"É necessário informar um cartão de crédito para esse tipo de transação.");
        $this->assertEquals(0, count(Transaction::all()));
        $this->assertEquals(0, count(Installment::all()));
    }

    public function test_users_can_not_store_a_credit_card_transaction_without_installments(): void
    {
        
        
        $this->post('/api/transactions',[
            'category_id' => 1,
            'type_id' => 2,
            'payment_method_id' => 4,
            'card_id' => 1,
            'transaction_description' => 'CURSO DE LARAVEL',
            'transaction_value' => 500.00,
            'date' => date('Y-m-d'),
        ]);

        $errors = session('errors');
        $this->assertEquals($errors->get('installments')[0],"É necessário informar a quantidade de parcelas para esse tipo de transação.");
        $this->assertEquals(0, count(Transaction::all()));
        $this->assertEquals(0, count(Installment::all()));
    }

    public function test_users_can_not_store_a_credit_card_transaction_with_an_invalid_card(): void
    {
        
        
        $this->post('/api/transactions',[
            'category_id' => 1,
            'type_id' => 2,
            'payment_method_id' => 4,
            'card_id' => 54,
            'transaction_description' => 'CURSO DE LARAVEL',
            'transaction_value' => 500.00,
            'date' => date('Y-m-d'),
            'installments' => 2
        ]);

        $this->assertEquals(0, count(Transaction::all()));
        $this->assertEquals(0, count(Installment::all()));
    }
}
