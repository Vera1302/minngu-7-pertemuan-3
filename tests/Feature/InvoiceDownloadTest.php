<?php

use App\Models\CashierTransaction;
use App\Models\User;

test('cashier can download a PDF invoice for their transaction', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);
    $transaction = CashierTransaction::create([
        'transaction_code' => 'TRX-20261029-ABC12345',
        'user_id' => $cashier->id,
        'total_amount' => 25000,
        'cash_received' => 30000,
        'change_amount' => 5000,
    ]);
    $transaction->items()->create([
        'product_name' => 'Beras Premium',
        'unit_price' => 12500,
        'quantity' => 2,
        'line_total' => 25000,
    ]);

    $response = $this->actingAs($cashier)
        ->get(route('kasir.transactions.invoice', $transaction));

    $response->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertHeader('content-disposition', 'attachment; filename=invoice_'.$transaction->id.'.pdf');

    expect($response->getContent())->toStartWith('%PDF');
});

test('cashier cannot download another cashiers transaction invoice', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);
    $otherCashier = User::factory()->create(['role' => 'kasir']);
    $transaction = CashierTransaction::create([
        'transaction_code' => 'TRX-20261029-ABC12345',
        'user_id' => $otherCashier->id,
        'total_amount' => 25000,
        'cash_received' => 30000,
        'change_amount' => 5000,
    ]);

    $this->actingAs($cashier)
        ->get(route('kasir.transactions.invoice', $transaction))
        ->assertNotFound();
});

test('admin cannot access cashier invoice download route', function () {
    $admin = User::factory()->admin()->create();
    $transaction = CashierTransaction::create([
        'transaction_code' => 'TRX-20261029-ABC12345',
        'total_amount' => 25000,
        'cash_received' => 30000,
        'change_amount' => 5000,
    ]);

    $this->actingAs($admin)
        ->get(route('kasir.transactions.invoice', $transaction))
        ->assertForbidden();
});

test('cashier dashboard provides an invoice download link', function () {
    $cashier = User::factory()->create(['role' => 'kasir']);

    $this->actingAs($cashier)
        ->get(route('kasir.dashboard'))
        ->assertOk()
        ->assertSee('Unduh invoice PDF')
        ->assertSee('__TRANSACTION__');
});
