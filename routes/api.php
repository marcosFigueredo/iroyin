<?php

use App\Http\Controllers\Api\ClimaController;
use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\EditalController;
use App\Http\Controllers\Api\HorarioController;
use App\Http\Controllers\Api\NoticiaController;
use Illuminate\Support\Facades\Route;

Route::get('/config',   [ConfigController::class, 'index']);
Route::get('/clima',    [ClimaController::class,  'index']);
Route::get('/horarios', [HorarioController::class, 'index']);
Route::get('/noticias', [NoticiaController::class, 'index']);
Route::get('/editais',  [EditalController::class,  'index']);
