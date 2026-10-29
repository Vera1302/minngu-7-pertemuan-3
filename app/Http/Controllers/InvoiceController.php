<?php

namespace App\Http\Controllers;

use App\Models\CashierTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function download(Request $request, CashierTransaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            abort(404);
        }

        $transaction->load(['items', 'cashier']);

        $pdf = Pdf::loadView('invoices.invoice', ['invoice' => $transaction]);

        return $pdf->download('invoice_'.$transaction->id.'.pdf');
    }
}
