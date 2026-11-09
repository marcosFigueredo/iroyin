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
use App\Http\Controllers\Admin\CampusController;
use App\Http\Controllers\Admin\DepartamentoConfigController;
use App\Http\Controllers\Admin\DepartamentoController;
use App\Http\Controllers\Admin\InformeController;
use App\Http\Controllers\Admin\OperarComoController;
use App\Http\Controllers\Admin\UnidadeController;
use App\Models\Departamento;
use App\Models\Instituicao;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('departamentos', [
        'departamentos' => Departamento::with('campus')->where('ativo', true)->orderBy('slug')->get(),
        'inst'          => Instituicao::current(),
    ]);
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
    Route::get('semestres', [SemestreController::class, 'index'])->name('semestres.index');
    Route::prefix('semestres/{semestre}')->name('semestres.')->middleware('perfil:gestor,departamento,unidade')->group(function () {
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
Route::prefix('admin')->name('admin.')->middleware(['auth', 'perfil:gestor'])->group(function () {
    Route::post('operar-como', OperarComoController::class)->name('operar-como');
    Route::resource('semestres', SemestreController::class)->only(['store', 'destroy']);
    Route::resource('editais', EditalController::class)->except(['show', 'create', 'edit']);
    Route::post('editais/{edital}/toggle', [EditalController::class, 'toggle'])->name('editais.toggle');
    Route::resource('agencias', AgenciaController::class)->only(['store', 'update', 'destroy']);
    Route::resource('noticias', NoticiaController::class)->except(['show']);
    Route::post('noticias/buscar-feed',  [NoticiaController::class, 'buscarFeed']) ->name('noticias.buscar-feed');
    Route::post('noticias/buscar-todos', [NoticiaController::class, 'buscarTodos'])->name('noticias.buscar-todos');
    Route::resource('campi', CampusController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('departamentos', DepartamentoController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('feeds', FeedController::class)->except(['show', 'create', 'edit']);
    Route::post('feeds/{feed}/toggle', [FeedController::class, 'toggle'])->name('feeds.toggle');

    Route::get('instituicao',  [InstituicaoController::class, 'edit'])  ->name('instituicao.edit');
    Route::put('instituicao',  [InstituicaoController::class, 'update'])->name('instituicao.update');

    Route::get('configuracoes',  [ConfiguracaoController::class, 'edit'])  ->name('configuracoes.edit');
    Route::put('configuracoes',  [ConfiguracaoController::class, 'update'])->name('configuracoes.update');
});

// Administração do departamento
Route::prefix('admin')->name('admin.')->middleware(['auth', 'perfil:gestor,departamento'])->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('unidades', UnidadeController::class)->except(['show', 'create']);
    Route::get('departamento-config', [DepartamentoConfigController::class, 'edit'])->name('departamento-config.edit');
    Route::put('departamento-config', [DepartamentoConfigController::class, 'update'])->name('departamento-config.update');
});

// Informes — Reitoria, gabinetes e secretarias
Route::prefix('admin')->name('admin.')->middleware(['auth', 'perfil:gestor,reitoria,departamento,unidade'])->group(function () {
    Route::resource('informes', InformeController::class)->except(['show']);
});

require __DIR__.'/auth.php';

// Monitor de cada departamento. Deve ser a última rota, para não capturar as do sistema.
Route::get('/{departamento:slug}', fn (Departamento $departamento) => response()->file(public_path('monitor.html')))
    ->where('departamento', '[a-z0-9-]+')
    ->name('monitor');
