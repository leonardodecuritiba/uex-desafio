<?php

use Illuminate\Support\Facades\Route;
use App\Infrastructure\Http\Controllers\Auth\RegisterController;
use App\Infrastructure\Http\Controllers\Auth\ForgotPasswordController;
use App\Infrastructure\Http\Controllers\Auth\ResetPasswordController;
use App\Infrastructure\Http\Controllers\Auth\LoginController;
use App\Infrastructure\Http\Controllers\Auth\LogoutController;
use App\Infrastructure\Http\Controllers\Auth\MeController;
use App\Infrastructure\Http\Controllers\Contacts\CreateContactController;
use App\Infrastructure\Http\Controllers\Contacts\UpdateContactController;
use App\Infrastructure\Http\Controllers\Contacts\DeleteContactController;
use App\Infrastructure\Http\Controllers\Contacts\SearchContactsController;
use App\Infrastructure\Http\Controllers\Contacts\ShowContactController;
use App\Infrastructure\Http\Controllers\Address\CepLookupController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use App\Infrastructure\Http\Controllers\Account\DeleteAccountController;

// Password reset (anti-enum response, rate-limited)
Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('/auth/register', RegisterController::class)->name('auth.register');
    Route::post('/auth/forgot-password', ForgotPasswordController::class)->name('auth.forgot');
    Route::post('/auth/reset-password', ResetPasswordController::class)->name('auth.reset');
});

// Login (rate-limited) — usando sessão; CSRF desativado aqui para simplificar testes
Route::middleware(['web', 'throttle:5,1'])->group(function () {
    Route::post('/auth/login', LoginController::class)
        ->name('auth.login')
        ->withoutMiddleware([VerifyCsrfToken::class]);
});

// Sessão atual e logout protegidos por Sanctum (sessão)
Route::middleware(['web', 'auth:sanctum'])->group(function () {
    Route::get('/auth/me', MeController::class)->name('auth.me');
    Route::post('/auth/logout', LogoutController::class)
        ->name('auth.logout')
        ->withoutMiddleware([VerifyCsrfToken::class]);

    // Excluir a própria conta
    Route::delete('/account', DeleteAccountController::class)
        ->middleware('throttle:5,1')
        ->name('account.delete')
        ->withoutMiddleware([VerifyCsrfToken::class]);

    // Contacts
    Route::get('/contacts', SearchContactsController::class)
        ->name('contacts.index')
        ->withoutMiddleware([VerifyCsrfToken::class]);
    Route::get('/contacts/{id}', ShowContactController::class)
        ->name('contacts.show')
        ->withoutMiddleware([VerifyCsrfToken::class]);
    Route::post('/contacts', CreateContactController::class)
        ->name('contacts.store')
        ->withoutMiddleware([VerifyCsrfToken::class]);
    Route::patch('/contacts/{id}', UpdateContactController::class)
        ->name('contacts.update')
        ->withoutMiddleware([VerifyCsrfToken::class]);
    Route::delete('/contacts/{id}', DeleteContactController::class)
        ->name('contacts.destroy')
        ->withoutMiddleware([VerifyCsrfToken::class]);
});

// Address CEP lookup (protegido por auth em prod; aqui mantemos em grupo auth)
Route::middleware(['web', 'auth:sanctum', 'throttle:30,1'])->group(function () {
    Route::get('/address/cep/{cep}', CepLookupController::class)->name('address.cep');
});

Route::get('/health', fn() => response()->json(['status' => 'ok']))->name('health');
