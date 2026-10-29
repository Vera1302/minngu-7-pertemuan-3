<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\CashierTransactionController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\KasirDashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StrukController;

/*
|--------------------------------------------------------------------------
| HALAMAN UTAMA
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});


/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

Route::get('/login', function () {
    return view('auth.login');
})->name('login');


/*
|--------------------------------------------------------------------------
| PROSES LOGIN
|--------------------------------------------------------------------------
*/

Route::post('/login', function (Request $request) {

    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {

        $request->session()->regenerate();

        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        if (Auth::user()->role == 'admin') {
            return redirect()->route('admin.dashboard');
        }

        /*
        |--------------------------------------------------------------------------
        | KASIR
        |--------------------------------------------------------------------------
        */

        if (Auth::user()->role == 'kasir') {
            return redirect()->route('kasir.dashboard');
        }

        Auth::logout();

        return redirect()
            ->route('login')
            ->withErrors([
                'email' => 'Role pengguna tidak valid.'
            ]);
    }

    return back()
        ->withErrors([
            'email' => 'Email atau password salah.'
        ])
        ->onlyInput('email');

})->name('login.process');


/*
|--------------------------------------------------------------------------
| HALAMAN ADMIN
|--------------------------------------------------------------------------
|
| URL: /admin
|
| Hanya role admin yang boleh masuk.
|
*/

Route::get('/admin', AdminDashboardController::class)
  ->middleware(['auth', 'admin'])
  ->name('admin');


/*
|--------------------------------------------------------------------------
| DASHBOARD ADMIN
|--------------------------------------------------------------------------
|
| URL: /admin/dashboard
|
*/

Route::get('/admin/dashboard', AdminDashboardController::class)
  ->middleware(['auth', 'admin'])
  ->name('admin.dashboard');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::resource('/products', ProductController::class)->names('products');
});


/*
|--------------------------------------------------------------------------
| HALAMAN KASIR
|--------------------------------------------------------------------------
|
| URL: /kasir
|
| Hanya role kasir yang boleh masuk.
|
*/

Route::get('/kasir', fn () => redirect()->route('kasir.dashboard'))
  ->middleware(['auth', 'kasir'])
  ->name('kasir');


/*
|--------------------------------------------------------------------------
| DASHBOARD KASIR
|--------------------------------------------------------------------------
|
| URL: /kasir/dashboard
|
*/

Route::get('/kasir/dashboard', KasirDashboardController::class)
  ->middleware(['auth', 'kasir'])
  ->name('kasir.dashboard');

Route::middleware(['auth', 'kasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/printers', [StrukController::class, 'printers'])->name('printers');
    Route::post('/transactions/{transaction}/receipt', [StrukController::class, 'printTransaction'])->name('transactions.receipt');
    Route::get('/transactions/{transaction}/invoice', [InvoiceController::class, 'download'])->name('transactions.invoice');
    Route::post('/transactions', [CashierTransactionController::class, 'store'])->name('transactions.store');
});


/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

Route::post('/logout', function (Request $request) {

    Auth::logout();

    $request->session()->invalidate();

    $request->session()->regenerateToken();

    return redirect()->route('login');

})->middleware('auth')->name('logout');