<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $inst->sigla }} · Departamentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5" style="max-width: 720px;">
        <h1 class="h3 fw-bold mb-1" style="color:#1a3369;">{{ $inst->nome ?: $inst->sigla }}</h1>
        <p class="text-muted mb-4">Escolha o departamento.</p>

        <div class="list-group shadow-sm">
            @forelse($departamentos as $dep)
                <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="/{{ $dep->slug }}">
                    <span><strong>{{ strtoupper($dep->slug) }}</strong> · {{ $dep->nome }}</span>
                    <span class="text-muted small">{{ $dep->campus->nome }}</span>
                </a>
            @empty
                <div class="list-group-item text-muted">Nenhum departamento cadastrado.</div>
            @endforelse
        </div>

        <p class="mt-4 small"><a href="{{ route('login') }}">Acesso ao painel</a></p>
    </main>
</body>
</html>
