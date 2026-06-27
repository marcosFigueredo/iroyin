<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IROYIN — Configuração Inicial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        html, body { height: 100%; }
        body { margin: 0; background-color: #f0f2f5; }
        .setup-page { min-height: 100vh; background-color: #f0f2f5; }
    </style>
</head>
<body>
    <main class="setup-page d-flex align-items-center py-5">
        <div class="container" style="max-width: 520px;">

            <div class="text-center mb-4">
                <div class="fw-black mb-1" style="font-size:2.2rem;color:#1a3369;letter-spacing:3px;">IROYIN</div>
                <p class="text-muted mb-0" style="font-size:0.85rem;letter-spacing:1px;">
                    SISTEMA DE INFORMAÇÃO INSTITUCIONAL
                </p>
            </div>

            <div class="card border-0 rounded-4 shadow-lg">
                <div class="card-body p-4 p-md-5">

                    <div class="mb-4">
                        <h2 class="h4 fw-bold mb-1" style="color:#1a3369;">
                            <i class="bi bi-gear-fill me-2"></i>Configuração Inicial
                        </h2>
                        <p class="text-muted small mb-0">
                            Crie a conta do administrador principal. Depois de entrar, configure os dados da sua instituição.
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger d-flex align-items-center gap-2 py-2">
                            <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('setup.store') }}">
                        @csrf

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name" id="name" value="{{ old('name') }}"
                                   placeholder="Nome completo" required autofocus autocomplete="name">
                            <label for="name">Nome completo</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   name="email" id="email" value="{{ old('email') }}"
                                   placeholder="E-mail" required autocomplete="email">
                            <label for="email">E-mail</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   name="password" id="password"
                                   placeholder="Senha (mínimo 8 caracteres)" required autocomplete="new-password">
                            <label for="password">Senha <span class="text-muted">(mínimo 8 caracteres)</span></label>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="password" class="form-control"
                                   name="password_confirmation" id="password_confirmation"
                                   placeholder="Confirmar senha" required autocomplete="new-password">
                            <label for="password_confirmation">Confirmar senha</label>
                        </div>

                        <div class="d-grid">
                            <button type="submit"
                                    class="btn btn-lg text-white fw-semibold rounded-pill"
                                    style="background-color: #1a3369;">
                                <i class="bi bi-check2-circle me-2"></i>Criar conta e entrar
                            </button>
                        </div>
                    </form>

                    <div class="border-top mt-4 pt-4">
                        <p class="small text-muted mb-0">
                            <i class="bi bi-shield-lock me-1"></i>
                            Esta página só está disponível na primeira execução do sistema.
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
