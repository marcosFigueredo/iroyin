<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $inst->sigla }} — Acesso ao Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        html, body { height: 100%; }
        body { margin: 0; background-color: #f0f2f5; }
        .login-page { height: 100vh; overflow: hidden; background-color: #f0f2f5; }
        .login-left { height: 100vh; display: flex; align-items: center; }
        .login-card-wrapper { height: 100vh; display: flex; align-items: center; }
        .login-card { width: 100%; }
        .login-left h1, .login-left p { color: #1a3369; }
        @media (max-width: 991.98px) {
            body { background-color: #f0f2f5; }
            .login-page { min-height: 100vh; height: auto; overflow: auto; padding: 1rem 0; }
            .login-card-wrapper { min-height: 100vh; height: auto; align-items: center; }
        }
    </style>
</head>
<body>
    <main class="login-page">
        <div class="container h-100">
            <div class="row h-100 align-items-center">

                {{-- Painel esquerdo --}}
                <div class="col-lg-7 d-none d-lg-block">
                    <div class="login-left pe-xl-5">
                        <div>
                            @if($inst->logo_url)
                                <img src="{{ asset($inst->logo_url) }}"
                                     alt="{{ $inst->sigla }}"
                                     class="img-fluid mb-2"
                                     style="max-width: 220px;">
                            @else
                                <div class="fw-black mb-2" style="font-size:2.5rem;color:#1a3369;letter-spacing:2px;">
                                    {{ $inst->sigla }}
                                </div>
                            @endif

                            <hr class="opacity-25 mb-4" style="border-color:#1a3369;">

                            <h1 class="display-6 fw-bold mb-4" style="color:#1a3369;">
                                {{ $inst->nome }}
                            </h1>

                            <p class="lead mb-4" style="color:#1a3369;">
                                {{ $inst->departamento }}
                            </p>

                            <p class="mb-4 text-muted">
                                {{ $inst->cidade }}{{ $inst->estado ? ', ' . $inst->estado : '' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Painel direito (formulário) --}}
                <div class="col-12 col-lg-5">
                    <div class="login-card-wrapper">
                        <div class="login-card">
                            <div class="card border-0 rounded-4 shadow-lg">
                                <div class="card-body p-4 p-md-5">

                                    <div class="mb-4 text-center text-lg-start">
                                        <h2 class="h3 fw-bold mb-2" style="color: #1a3369;">
                                            Acesso ao Sistema
                                        </h2>
                                        <p class="text-muted mb-0">
                                            Entre com seu e-mail e senha cadastrados.
                                        </p>
                                    </div>

                                    @if (session('status'))
                                        <div class="alert alert-success">{{ session('status') }}</div>
                                    @endif

                                    @if ($errors->any())
                                        <div class="alert alert-danger d-flex align-items-center gap-2 py-2">
                                            <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                                            <span>{{ $errors->first() }}</span>
                                        </div>
                                    @endif

                                    <form method="POST" action="{{ route('login') }}">
                                        @csrf

                                        <div class="form-floating mb-3">
                                            <input type="email" class="form-control" name="email" id="email"
                                                   value="{{ old('email') }}" placeholder="seu@email.com"
                                                   required autofocus autocomplete="username">
                                            <label for="email">E-mail</label>
                                        </div>

                                        <div class="form-floating mb-3">
                                            <input type="password" class="form-control" name="password" id="password"
                                                   placeholder="Senha" required autocomplete="current-password">
                                            <label for="password">Senha</label>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                                                <label class="form-check-label text-secondary" for="remember_me">
                                                    Manter conectado
                                                </label>
                                            </div>
                                        </div>

                                        <div class="d-grid">
                                            <button type="submit"
                                                    class="btn btn-lg text-white fw-semibold rounded-pill"
                                                    style="background-color: #1a3369;">
                                                Entrar
                                            </button>
                                        </div>
                                    </form>

                                    <div class="border-top mt-4 pt-4">
                                        <p class="small text-muted mb-0">
                                            Acesso restrito a usuários cadastrados pelo administrador do sistema.
                                        </p>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</body>
</html>
