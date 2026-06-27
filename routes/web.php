<?php

use App\Http\Controllers\Admin\AgenciaController;
use App\Http\Controllers\Admin\ConfiguracaoController;
use App\Http\Controllers\Admin\EditalController;
use App\Http\Controllers\Admin\FeedController;
use App\Http\Controllers\Admin\HorarioController;
use App\Http\Controllers\Admin\InstituicaoController;
use App\Http\Controllers\Admin\NoticiaController;
use App\Http\Controllers\Admin\SemestreController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AcessivelController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SetupController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->file(public_path('index.html'));
});

Route::get('/acessivel', [AcessivelController::class, 'index'])->name('acessivel');

// Wizard de primeira configuração — acessível somente sem usuários cadastrados
Route::get('/setup',  [SetupController::class, 'index'])->name('setup.index');
Route::post('/setup', [SetupController::class, 'store'])->name('setup.store');

Route::get('/admin', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Conteúdo — editores e admins
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::resource('editais', EditalController::class)->except(['show', 'create', 'edit']);
    Route::post('editais/{edital}/toggle', [EditalController::class, 'toggle'])->name('editais.toggle');
    Route::resource('agencias', AgenciaController::class)->only(['store', 'update', 'destroy']);
    Route::resource('noticias', NoticiaController::class)->except(['show']);
    Route::post('noticias/buscar-feed',  [NoticiaController::class, 'buscarFeed']) ->name('noticias.buscar-feed');
    Route::post('noticias/buscar-todos', [NoticiaController::class, 'buscarTodos'])->name('noticias.buscar-todos');
    Route::resource('semestres', SemestreController::class)->except(['show','create','edit','update']);
    Route::post('semestres/{semestre}/ativar', [SemestreController::class, 'ativar'])->name('semestres.ativar');
    Route::prefix('semestres/{semestre}')->name('semestres.')->group(function () {
        Route::get('horarios',                [HorarioController::class, 'index'])   ->name('horarios.index');
        Route::get('horarios/create',         [HorarioController::class, 'create'])  ->name('horarios.create');
        Route::post('horarios',               [HorarioController::class, 'store'])   ->name('horarios.store');
        Route::get('horarios/{horario}/edit', [HorarioController::class, 'edit'])    ->name('horarios.edit');
        Route::put('horarios/{horario}',      [HorarioController::class, 'update'])  ->name('horarios.update');
        Route::delete('horarios/{horario}',   [HorarioController::class, 'destroy']) ->name('horarios.destroy');
        Route::post('horarios/importar',      [HorarioController::class, 'importar'])->name('horarios.importar');
    });
});

// Configuração — somente admins
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('feeds', FeedController::class)->except(['show', 'create', 'edit']);
    Route::post('feeds/{feed}/toggle', [FeedController::class, 'toggle'])->name('feeds.toggle');

    Route::get('instituicao',  [InstituicaoController::class, 'edit'])  ->name('instituicao.edit');
    Route::put('instituicao',  [InstituicaoController::class, 'update'])->name('instituicao.update');

    Route::get('configuracoes',  [ConfiguracaoController::class, 'edit'])  ->name('configuracoes.edit');
    Route::put('configuracoes',  [ConfiguracaoController::class, 'update'])->name('configuracoes.update');
});

require __DIR__.'/auth.php';
