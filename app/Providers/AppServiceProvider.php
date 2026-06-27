<?php

namespace App\Providers;

use App\Models\Instituicao;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Compartilha $inst com o layout do admin e com a tela de login
        View::composer(['layouts.app', 'auth.login'], function ($view) {
            $view->with('inst', Instituicao::current());
        });
    }
}
