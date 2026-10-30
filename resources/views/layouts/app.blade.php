<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MiniMarket') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-100 font-sans text-slate-800 antialiased">
        <div class="min-h-screen bg-slate-100">
            <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/85 backdrop-blur-xl">
                <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <a href="{{ url('/') }}" class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-lg font-black text-slate-900 shadow-lg shadow-amber-200">M</div>
                        <div>
                            <p class="text-lg font-black tracking-tight text-slate-900">MiniMarket</p>
                            <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-slate-500">Fresh & Fast</p>
                        </div>
                    </a>

                    <nav class="hidden items-center gap-7 text-sm font-semibold text-slate-600 md:flex">
                        <a href="{{ url('/') }}" class="transition hover:text-slate-900">Beranda</a>
                        <a href="{{ route('products.index') }}" class="transition hover:text-slate-900">Katalog</a>
                        <a href="{{ url('/#promo') }}" class="transition hover:text-slate-900">Promo</a>
                        <a href="{{ url('/#tentang') }}" class="transition hover:text-slate-900">Tentang</a>
                    </nav>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('products.index') }}" class="hidden rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:inline-flex">Catalog</a>
                        <a href="{{ route('products.create') }}" class="inline-flex rounded-full bg-slate-900 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-slate-200 transition hover:bg-slate-700">Tambah Produk</a>
                    </div>
                </div>
            </header>

            <main>
                @yield('content')
                {{ $slot ?? '' }}
            </main>

            <footer class="border-t border-slate-200 bg-white">
                <div class="mx-auto flex max-w-7xl flex-col gap-5 px-4 py-8 text-sm text-slate-600 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                    <div>
                        <p class="text-lg font-black text-slate-900">MiniMarket</p>
                        <p class="mt-1">Belanja kebutuhan keluarga dengan lebih mudah.</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-5">
                        <a href="{{ url('/') }}" class="transition hover:text-slate-900">Beranda</a>
                        <a href="{{ route('products.index') }}" class="transition hover:text-slate-900">Katalog</a>
                        <a href="{{ url('/#promo') }}" class="transition hover:text-slate-900">Promo</a>
                        <a href="{{ auth()->user()?->role === 'kasir' ? route('kasir.dashboard') : route('admin.dashboard') }}" class="transition hover:text-slate-900">Dashboard</a>
                    </div>

                    <p class="font-medium text-slate-500">© 2026 MiniMarket</p>
                </div>
            </footer>
        </div>
        <script>
    window.Echo.private('user.{{ auth()->id() }}')
        .listen('.OrderPlaced', (e) => {
            alert('Order #' + e.order_id + ' telah ditempatkan! Total: Rp ' + e.total);
            console.log('Order Placed:', e);
        });
</script>
    </body>
</html>
