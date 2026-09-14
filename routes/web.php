<?php

use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Portal público — sem autenticação
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/{municipio}', [PortalController::class, 'index'])->name('index');
    Route::get('/{municipio}/{conselho}', [PortalController::class, 'conselho'])->name('conselho');

    // Listas paginadas
    Route::get('/{municipio}/{conselho}/documentos', [PortalController::class, 'documentos'])->name('documentos');
    Route::get('/{municipio}/{conselho}/legislacao', [PortalController::class, 'legislacao'])->name('legislacao');
    Route::get('/{municipio}/{conselho}/reunioes', [PortalController::class, 'reunioes'])->name('reunioes');
    Route::get('/{municipio}/{conselho}/atos-normativos', [PortalController::class, 'atosNormativos'])->name('atos-normativos');
});
