<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->transaction_code }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #ccc;
            padding: 8px;
        }

        .details {
            margin-bottom: 20px;
        }

        .details p {
            margin: 4px 0;
        }

        .total {
            font-weight: bold;
            text-align: right;
        }

        .payment {
            margin-top: 16px;
            margin-left: auto;
            width: 40%;
        }

        .payment td {
            padding: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>INVOICE</h2>
        <p>No: {{ $invoice->transaction_code }}</p>
        <p>Tanggal: {{ $invoice->created_at->format('d/m/Y') }}</p>
    </div>

    <div class="details">
        <p>Kasir: {{ $invoice->cashier?->name ?? '-' }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Harga Satuan</th>
                <th>Jumlah</th>
                <th>Total</th>
            </tr>
        </thead>

        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>Rp {{ number_format((float) $item->line_total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>

        <tfoot>
            <tr>
                <td colspan="3" class="total">Grand Total:</td>
                <td class="total">
                    Rp {{ number_format((float) $invoice->total_amount, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    <table class="payment">
        <tr>
            <td>Tunai</td>
            <td class="total">Rp {{ number_format((float) $invoice->cash_received, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Kembalian</td>
            <td class="total">Rp {{ number_format((float) $invoice->change_amount, 0, ',', '.') }}</td>
        </tr>
    </table>
</body>
</html>