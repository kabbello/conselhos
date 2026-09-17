<?php

use App\Http\Controllers\Comissoes\ComissaoReuniaoPresencaPdfController;
use App\Http\Controllers\Auth\PrimeiroAcessoController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\Reunioes\ListaPresencaPdfController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'institucional')->name('institucional');

// Rotas autenticadas
Route::middleware(['auth'])->group(function () {
    Route::get('/reunioes/{reuniao}/lista-presenca', ListaPresencaPdfController::class)
        ->name('reunioes.lista-presenca-pdf');

    Route::get('/comissao-reunioes/{reuniao}/lista-presenca', ComissaoReuniaoPresencaPdfController::class)
        ->name('comissao-reunioes.lista-presenca-pdf');
});

// Primeiro acesso para conselheiros — sem autenticação
Route::get('/painel/primeiro-acesso', [PrimeiroAcessoController::class, 'show'])
    ->name('auth.primeiro-acesso');
Route::post('/painel/primeiro-acesso', [PrimeiroAcessoController::class, 'store'])
    ->name('auth.primeiro-acesso.store');

// Portal público — sem autenticação
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/{municipio}', [PortalController::class, 'index'])->name('index');
    Route::get('/{municipio}/{conselho}', [PortalController::class, 'conselho'])->name('conselho');

    // Listas paginadas
    Route::get('/{municipio}/{conselho}/documentos', [PortalController::class, 'documentos'])->name('documentos');
    Route::get('/{municipio}/{conselho}/legislacao', [PortalController::class, 'legislacao'])->name('legislacao');
    Route::get('/{municipio}/{conselho}/reunioes', [PortalController::class, 'reunioes'])->name('reunioes');
    Route::get('/{municipio}/{conselho}/atos-normativos', [PortalController::class, 'atosNormativos'])->name('atos-normativos');

    // Formulário de reporte de erro
    Route::post('/{municipio}/reportar-erro', [PortalController::class, 'reportarErro'])->name('reportar-erro');
});
