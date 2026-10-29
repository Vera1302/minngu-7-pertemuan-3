<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Kasir | MiniMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('kasir.dashboard') }}">MiniMarket</a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn btn-outline-light btn-sm" type="submit">Keluar</button>
            </form>
        </div>
    </nav>

    <main class="container py-5">
        <div class="mb-4">
            <p class="text-uppercase text-success fw-bold mb-1">Area Kasir</p>
            <h1 class="h2">Dashboard Kasir</h1>
            <p class="text-secondary mb-0">Selamat datang, {{ Auth::user()->name }}.</p>
        </div>

        <section class="bg-white border rounded-3 p-4" aria-labelledby="account-heading">
            <h2 class="h5" id="account-heading">Informasi akun</h2>
            <dl class="row mb-0">
                <dt class="col-sm-3">Nama</dt>
                <dd class="col-sm-9">{{ Auth::user()->name }}</dd>
                <dt class="col-sm-3">Email</dt>
                <dd class="col-sm-9">{{ Auth::user()->email }}</dd>
                <dt class="col-sm-3">Role</dt>
                <dd class="col-sm-9 mb-0">{{ Auth::user()->role }}</dd>
            </dl>
        </section>

        <section class="mt-4" aria-labelledby="cashier-products-heading">
            <div class="mb-3">
                <h2 class="h5 mb-1" id="cashier-products-heading">Produk tersedia</h2>
                <p class="text-secondary mb-0">Pilih produk untuk memasukkannya ke keranjang.</p>
            </div>
            @if ($products->isEmpty())
                <div class="bg-white border rounded-3 p-4 text-secondary">Belum ada produk aktif untuk dijual.</div>
            @else
                <div class="row g-3">
                    @foreach ($products as $product)
                        <div class="col-sm-6 col-lg-4">
                            <article class="bg-white border rounded-3 p-3 h-100">
                                <div class="d-flex justify-content-between gap-2">
                                    <h3 class="h6 mb-2">{{ $product->name }}</h3>
                                    <span class="badge {{ $product->stock > 0 ? 'text-bg-success' : 'text-bg-danger' }}">
                                        {{ $product->stock > 0 ? 'Tersedia' : 'Habis' }}
                                    </span>
                                </div>
                                <p class="small text-secondary mb-2">{{ $product->category ?: 'Umum' }}</p>
                                <p class="fw-bold mb-1">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <p class="small text-secondary mb-0">Stok: <span data-product-stock="{{ $product->id }}">{{ $product->stock }}</span></p>
                                    <button
                                        class="btn btn-sm btn-success add-to-cart"
                                        type="button"
                                        data-product-id="{{ $product->id }}"
                                        data-product-name="{{ $product->name }}"
                                        data-product-price="{{ $product->price }}"
                                        data-product-stock="{{ $product->stock }}"
                                        @disabled($product->stock < 1)
                                    >
                                        {{ $product->stock > 0 ? 'Pilih barang' : 'Stok habis' }}
                                    </button>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="bg-white border rounded-3 p-4 mt-4" aria-labelledby="cart-heading">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <h2 class="h5 mb-1" id="cart-heading">Keranjang belanja</h2>
                    <p class="small text-secondary mb-0"><span id="cart-count">0</span> barang dipilih</p>
                </div>
                <button class="btn btn-sm btn-outline-danger" type="button" id="clear-cart" disabled>Kosongkan</button>
            </div>
            <div id="cart-items" class="d-grid gap-2">
                <p class="text-secondary mb-0" id="empty-cart">Belum ada barang di keranjang.</p>
            </div>
            <hr>
            <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                <span>Total</span>
                <span id="cart-total">Rp 0</span>
            </div>
            <div class="row g-3 align-items-end">
                <div class="col-sm-6">
                    <label class="form-label" for="cash-received">Uang diterima</label>
                    <input class="form-control" type="number" id="cash-received" min="0" step="0.01" placeholder="Masukkan pembayaran" disabled>
                    <div class="form-text" id="change-preview">Kembalian: Rp 0</div>
                </div>
                <div class="col-sm-6 d-grid">
                    <button class="btn btn-primary btn-lg" type="button" id="checkout" disabled>Checkout</button>
                </div>
            </div>
            <div class="small mt-3" id="checkout-status" role="status" aria-live="polite"></div>
            <div class="alert alert-success mt-3 mb-0" id="receipt-summary" hidden aria-live="polite"></div>
        </section>

        <section class="bg-white border rounded-3 p-4 mt-4" aria-labelledby="printer-heading">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                <div>
                    <h2 class="h5 mb-1" id="printer-heading">Printer struk</h2>
                    <p class="text-secondary mb-0" id="selected-printer">Belum ada printer yang dipilih.</p>
                </div>
                <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#printerModal">
                    Pilih printer
                </button>
            </div>
        </section>
    </main>

    <div class="modal fade" id="printerModal" tabindex="-1" aria-labelledby="printerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="printerModalLabel">Pilih printer struk</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary">Pilih printer ESC/POS yang sudah dipasang dan dibagikan di Windows.</p>
                    <label class="form-label" for="printer-select">Printer tersedia</label>
                    <select class="form-select" id="printer-select" disabled>
                        <option value="">Memuat daftar printer...</option>
                    </select>
                    <div class="small mt-3" id="printer-status" role="status" aria-live="polite"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary me-auto" type="button" id="refresh-printers">Muat ulang</button>
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Selesai</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (() => {
            const cart = new Map();
            const cartItems = document.getElementById('cart-items');
            const emptyCart = document.getElementById('empty-cart');
            const cartCount = document.getElementById('cart-count');
            const cartTotal = document.getElementById('cart-total');
            const cashReceived = document.getElementById('cash-received');
            const changePreview = document.getElementById('change-preview');
            const checkoutButton = document.getElementById('checkout');
            const clearCartButton = document.getElementById('clear-cart');
            const checkoutStatus = document.getElementById('checkout-status');
            const receiptSummary = document.getElementById('receipt-summary');
            const savedPrinterKey = 'minimarket.kasir.printer';
            let lastTransactionId = null;
            const rupiah = amount => new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0,
            }).format(amount);
            const getTotal = () => [...cart.values()].reduce((sum, item) => sum + item.priceCents * item.quantity, 0);

            const renderCart = () => {
                cartItems.replaceChildren();
                emptyCart.hidden = cart.size !== 0;

                let count = 0;
                for (const item of cart.values()) {
                    count += item.quantity;
                    const row = document.createElement('div');
                    row.className = 'd-flex flex-wrap justify-content-between align-items-center gap-2 border rounded p-3';

                    const details = document.createElement('div');
                    const name = document.createElement('div');
                    name.className = 'fw-semibold';
                    name.textContent = item.name;
                    const subtotal = document.createElement('div');
                    subtotal.className = 'small text-secondary';
                    subtotal.textContent = `${rupiah(item.priceCents / 100)} × ${item.quantity} = ${rupiah(item.priceCents * item.quantity / 100)}`;
                    details.append(name, subtotal);

                    const controls = document.createElement('div');
                    controls.className = 'd-flex align-items-center gap-2';
                    const decrease = document.createElement('button');
                    decrease.className = 'btn btn-sm btn-outline-secondary';
                    decrease.type = 'button';
                    decrease.textContent = '−';
                    decrease.setAttribute('aria-label', `Kurangi ${item.name}`);
                    decrease.addEventListener('click', () => changeQuantity(item.id, -1));
                    const quantity = document.createElement('span');
                    quantity.className = 'fw-semibold';
                    quantity.textContent = String(item.quantity);
                    const increase = document.createElement('button');
                    increase.className = 'btn btn-sm btn-outline-secondary';
                    increase.type = 'button';
                    increase.textContent = '+';
                    increase.setAttribute('aria-label', `Tambah ${item.name}`);
                    increase.disabled = item.quantity >= item.stock;
                    increase.addEventListener('click', () => changeQuantity(item.id, 1));
                    const remove = document.createElement('button');
                    remove.className = 'btn btn-sm btn-outline-danger';
                    remove.type = 'button';
                    remove.textContent = 'Hapus';
                    remove.addEventListener('click', () => {
                        cart.delete(item.id);
                        renderCart();
                    });
                    controls.append(decrease, quantity, increase, remove);
                    row.append(details, controls);
                    cartItems.append(row);
                }

                const totalCents = getTotal();
                cartCount.textContent = String(count);
                cartTotal.textContent = rupiah(totalCents / 100);
                cashReceived.disabled = cart.size === 0;
                if (cart.size === 0) {
                    cashReceived.value = '';
                }
                clearCartButton.disabled = cart.size === 0;
                checkoutButton.disabled = cart.size === 0 || (Number(cashReceived.value) * 100) < totalCents;
                updateChangePreview();
            };

            const updateChangePreview = () => {
                const total = getTotal();
                const cashCents = Math.round((Number(cashReceived.value) || 0) * 100);
                changePreview.textContent = `Kembalian: ${rupiah(Math.max(0, cashCents - total) / 100)}`;
                checkoutButton.disabled = cart.size === 0 || cashCents < total;
            };

            const changeQuantity = (id, difference) => {
                const item = cart.get(id);
                if (!item) return;
                const quantity = item.quantity + difference;
                if (quantity < 1) {
                    cart.delete(id);
                } else if (quantity <= item.stock) {
                    item.quantity = quantity;
                }
                renderCart();
            };

            document.querySelectorAll('.add-to-cart').forEach(button => {
                button.addEventListener('click', () => {
                    const id = Number(button.dataset.productId);
                    const item = cart.get(id) || {
                        id,
                        name: button.dataset.productName,
                        priceCents: Math.round(Number(button.dataset.productPrice) * 100),
                        stock: Number(button.dataset.productStock),
                        quantity: 0,
                    };

                    if (item.quantity >= item.stock) {
                        checkoutStatus.textContent = `Stok ${item.name} hanya ${item.stock}.`;
                        checkoutStatus.className = 'small mt-3 text-danger';
                        return;
                    }

                    item.quantity++;
                    cart.set(id, item);
                    checkoutStatus.textContent = '';
                    receiptSummary.hidden = true;
                    renderCart();
                });
            });

            clearCartButton.addEventListener('click', () => {
                cart.clear();
                checkoutStatus.textContent = '';
                renderCart();
            });
            cashReceived.addEventListener('input', updateChangePreview);

            checkoutButton.addEventListener('click', async () => {
                if (cart.size === 0) return;
                checkoutButton.disabled = true;
                checkoutStatus.textContent = 'Menyimpan transaksi dan memperbarui stok...';
                checkoutStatus.className = 'small mt-3 text-secondary';

                try {
                    const response = await fetch(@json(route('kasir.transactions.store')), {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            items: [...cart.values()].map(item => ({
                                product_id: item.id,
                                quantity: item.quantity,
                            })),
                            cash_received: cashReceived.value,
                            printer: localStorage.getItem(savedPrinterKey),
                        }),
                    });
                    const result = await response.json();

                    if (!response.ok) {
                        const validationMessage = Object.values(result.errors || {}).flat()[0];
                        throw new Error(validationMessage || result.message || 'Checkout gagal.');
                    }

                    const sale = result.transaction;
                    const lines = sale.items.map(item =>
                        `${item.name} × ${item.quantity} — ${rupiah(Number(item.line_total))}`
                    );
                    receiptSummary.replaceChildren();
                    const receiptTitle = document.createElement('strong');
                    receiptTitle.textContent = `Transaksi berhasil · ${sale.code}`;
                    const receiptBody = document.createElement('div');
                    receiptBody.className = 'mt-2';
                    receiptBody.textContent = `${lines.join(' | ')} | Total ${rupiah(Number(sale.total))} | Tunai ${rupiah(Number(sale.cash_received))} | Kembalian ${rupiah(Number(sale.change))}`;
                    const receiptStatus = document.createElement('div');
                    receiptStatus.className = `small mt-2 ${sale.receipt.printed ? 'text-success' : 'text-warning-emphasis'}`;
                    receiptStatus.textContent = sale.receipt.message;
                    const printReceiptButton = document.createElement('button');
                    printReceiptButton.className = 'btn btn-sm btn-outline-success mt-3';
                    printReceiptButton.type = 'button';
                    printReceiptButton.textContent = 'Cetak struk transaksi';
                    lastTransactionId = sale.id;
                    printReceiptButton.addEventListener('click', async () => {
                        const savedPrinter = localStorage.getItem(savedPrinterKey);
                        if (!savedPrinter) {
                            receiptStatus.textContent = 'Pilih printer melalui tombol Pilih printer terlebih dahulu.';
                            receiptStatus.className = 'small mt-2 text-danger';
                            return;
                        }

                        printReceiptButton.disabled = true;
                        receiptStatus.textContent = 'Mengirim struk transaksi ke printer...';
                        receiptStatus.className = 'small mt-2 text-secondary';

                        try {
                            const printResponse = await fetch(@json(route('kasir.transactions.receipt', ['transaction' => '__TRANSACTION__'])).replace('__TRANSACTION__', String(lastTransactionId)), {
                                method: 'POST',
                                headers: {
                                    Accept: 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify({ printer: savedPrinter }),
                            });
                            const printResult = await printResponse.json();
                            if (!printResponse.ok) {
                                throw new Error(printResult.message || printResult.errors?.printer?.[0] || 'Struk transaksi gagal dicetak.');
                            }
                            receiptStatus.textContent = printResult.message;
                            receiptStatus.className = 'small mt-2 text-success';
                        } catch (error) {
                            receiptStatus.textContent = error.message || 'Struk transaksi gagal dicetak.';
                            receiptStatus.className = 'small mt-2 text-danger';
                        } finally {
                            printReceiptButton.disabled = false;
                        }
                    });
                    const invoiceLink = document.createElement('a');
                    invoiceLink.className = 'btn btn-sm btn-outline-primary mt-3 ms-2';
                    invoiceLink.href = @json(route('kasir.transactions.invoice', ['transaction' => '__TRANSACTION__'])).replace('__TRANSACTION__', String(sale.id));
                    invoiceLink.textContent = 'Unduh invoice PDF';
                    receiptSummary.append(receiptTitle, receiptBody, receiptStatus, printReceiptButton, invoiceLink);
                    receiptSummary.hidden = false;

                    sale.items.forEach(item => {
                        const productButton = document.querySelector(`.add-to-cart[data-product-id="${item.product_id}"]`);
                        if (productButton) {
                            productButton.dataset.productStock = String(item.stock_after);
                            productButton.disabled = item.stock_after < 1;
                            productButton.textContent = item.stock_after > 0 ? 'Pilih barang' : 'Stok habis';
                            document.querySelector(`[data-product-stock="${item.product_id}"]`).textContent = String(item.stock_after);
                        }
                    });

                    cart.clear();
                    cashReceived.value = '';
                    checkoutStatus.textContent = 'Transaksi tersimpan dan stok berhasil diperbarui.';
                    checkoutStatus.className = 'small mt-3 text-success';
                    renderCart();
                } catch (error) {
                    checkoutStatus.textContent = error.message || 'Checkout gagal.';
                    checkoutStatus.className = 'small mt-3 text-danger';
                    checkoutButton.disabled = false;
                }
            });

            renderCart();

            const printerModal = document.getElementById('printerModal');
            const printerSelect = document.getElementById('printer-select');
            const printerStatus = document.getElementById('printer-status');
            const selectedPrinter = document.getElementById('selected-printer');
            let availablePrinters = [];

            const setStatus = (message, isError = false) => {
                printerStatus.textContent = message;
                printerStatus.className = `small mt-3 ${isError ? 'text-danger' : 'text-secondary'}`;
            };

            const updateSelectedPrinter = () => {
                const printer = availablePrinters.find(item => item.share_name === printerSelect.value);
                selectedPrinter.textContent = printer
                    ? `Printer dipilih: ${printer.name}`
                    : 'Belum ada printer yang dipilih.';

                if (printer) {
                    localStorage.setItem(savedPrinterKey, printer.share_name);
                } else {
                    localStorage.removeItem(savedPrinterKey);
                }
            };

            const loadPrinters = async () => {
                printerSelect.disabled = true;
                printerSelect.replaceChildren(new Option('Memuat daftar printer...', ''));
                setStatus('');

                try {
                    const response = await fetch(@json(route('kasir.printers')), {
                        headers: { Accept: 'application/json' },
                    });
                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Daftar printer gagal dimuat.');
                    }

                    availablePrinters = result.printers;
                    printerSelect.replaceChildren();

                    if (availablePrinters.length === 0) {
                        printerSelect.add(new Option('Tidak ada printer Windows yang dibagikan', ''));
                        setStatus('Bagikan printer struk di pengaturan Windows, lalu muat ulang.', true);
                        return;
                    }

                    printerSelect.add(new Option('Pilih printer...', ''));
                    availablePrinters.forEach(printer => {
                        printerSelect.add(new Option(printer.name, printer.share_name));
                    });
                    printerSelect.disabled = false;

                    const savedPrinter = localStorage.getItem(savedPrinterKey);
                    if (availablePrinters.some(printer => printer.share_name === savedPrinter)) {
                        printerSelect.value = savedPrinter;
                    }

                    updateSelectedPrinter();
                } catch (error) {
                    printerSelect.replaceChildren(new Option('Daftar printer tidak tersedia', ''));
                    setStatus(error.message || 'Daftar printer gagal dimuat.', true);
                }
            };

            printerModal.addEventListener('show.bs.modal', loadPrinters);
            document.getElementById('refresh-printers').addEventListener('click', loadPrinters);
            printerSelect.addEventListener('change', updateSelectedPrinter);
        })();
    </script>
</body>
</html>