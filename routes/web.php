<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Auth\UnifiedLoginController;

/*
|--------------------------------------------------------------------------
| Página Inicial - Mostra o formulário de login
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (auth()->check()) {
        return app(UnifiedLoginController::class)->redirectToPanel(auth()->user());
    }
    return view('auth.login');
})->name('home');

/*
|--------------------------------------------------------------------------
| Rotas de Autenticação Unificada
|--------------------------------------------------------------------------
| Todos os utilizadores usam a mesma rota de login.
| Após autenticação, são redirecionados para o painel correto baseado no role.
*/
Route::get('/login', function () {
    if (auth()->check()) {
        return app(UnifiedLoginController::class)->redirectToPanel(auth()->user());
    }
    return view('auth.login');
})->name('login');

Route::post('/login', [UnifiedLoginController::class, 'login']);

Route::post('/logout', [UnifiedLoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| PDF Reports (protected by auth)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/student-list/{institution}', [ReportController::class, 'studentList'])->name('student-list');
    Route::get('/march-guide/{student}', [ReportController::class, 'marchGuide'])->name('march-guide');
    Route::get('/approved-candidates', [ReportController::class, 'approvedCandidates'])->name('approved-candidates');
    Route::get('/student-grades/{class}', [ReportController::class, 'studentGrades'])->name('student-grades');
    Route::get('/absence-report/{institution}', [ReportController::class, 'absenceReport'])->name('absence-report');
    Route::get('/student-history/{student}', [ReportController::class, 'studentHistory'])->name('student-history');
});
