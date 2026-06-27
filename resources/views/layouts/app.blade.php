<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .sidebar { width: 260px; min-height: calc(100vh - 72px); background: #e4e7ec; border-right: 1px solid #d1d5db; }
        .sidebar .nav-link { color: #374151; font-size: 0.875rem; font-weight: 500; border-radius: 6px; padding: 8px 12px; }
        .sidebar .nav-link:hover { background-color: #d1d5db; color: #1a3369; }
        .sidebar .nav-link.active { background-color: #c8cfe0; color: #1a3369; font-weight: 600; }
        .sidebar .nav-label { font-size: 0.7rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.08em; padding: 0 12px; margin-top: 16px; margin-bottom: 4px; }
        .main-header { height: 72px; background-color: #f0f2f5; border-bottom: 1px solid #d1d5db; }
        .content-area { min-height: calc(100vh - 72px); }
    </style>
</head>
<body>

    {{-- Header --}}
    <header class="main-header d-flex align-items-center px-4 shadow">
        <div class="d-flex align-items-center gap-3 flex-grow-1">
            @if($inst->logo_url)
                <img src="{{ asset($inst->logo_url) }}" alt="{{ $inst->sigla }}" style="height: 48px; width: auto; object-fit: contain;">
            @endif
            <span class="fw-bold fs-5" style="color:#1a3369;">{{ $inst->sigla }}</span>
        </div>

        <div class="dropdown">
            <button class="btn btn-link text-decoration-none d-flex align-items-center gap-2"
                    style="color:#1a3369;" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle fs-4"></i>
                <span class="small fw-semibold">{{ auth()->user()->name }}</span>
                <i class="bi bi-chevron-down small"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><span class="dropdown-item-text small text-muted">{{ auth()->user()->email }}</span></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>Sair
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </header>

    <div class="d-flex content-area">

        {{-- Sidebar --}}
        <aside class="sidebar p-3 flex-shrink-0">

            {{-- Usuário logado --}}
            <div class="d-flex align-items-center gap-2 px-2 py-3 mb-2 border-bottom">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                     style="width:38px;height:38px;background:#1a3369;font-size:15px;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div style="min-width:0;">
                    <div class="fw-semibold text-truncate" style="font-size:13px;color:#1a3369;">{{ auth()->user()->name }}</div>
                    <div class="text-muted" style="font-size:11px;">{{ auth()->user()->role === 'admin' ? 'Administrador' : 'Editor' }}</div>
                </div>
            </div>

            {{-- Menu --}}
            <nav>
                <div class="nav-label">Painel</div>
                <a href="{{ route('dashboard') }}"
                   class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>

                <div class="nav-label">Conteúdo</div>
                <a href="{{ route('admin.semestres.index') }}"
                   class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('admin.semestres.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar3"></i> Horários
                </a>
                <a href="{{ route('admin.noticias.index') }}"
                   class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('admin.noticias.*') ? 'active' : '' }}">
                    <i class="bi bi-newspaper"></i> Notícias
                </a>
                <a href="{{ route('admin.editais.index') }}"
                   class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('admin.editais.*', 'admin.agencias.*') ? 'active' : '' }}">
                    <i class="bi bi-megaphone"></i> Editais
                </a>

                @if(auth()->user()->isAdmin())
                <div class="nav-label">Sistema</div>
                <a href="{{ route('admin.feeds.index') }}"
                   class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('admin.feeds.*') ? 'active' : '' }}">
                    <i class="bi bi-rss"></i> Fontes RSS
                </a>
                <a href="{{ route('admin.users.index') }}"
                   class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i> Usuários
                </a>
                <a href="{{ route('admin.instituicao.edit') }}"
                   class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('admin.instituicao.*') ? 'active' : '' }}">
                    <i class="bi bi-building"></i> Instituição
                </a>
                <a href="{{ route('admin.configuracoes.edit') }}"
                   class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('admin.configuracoes.*') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> Configurações
                </a>
                @endif
            </nav>
        </aside>

        {{-- Conteúdo principal --}}
        <main class="flex-grow-1 p-4">
            @if(isset($header))
                <h4 class="fw-bold mb-4" style="color:#1a3369;">{{ $header }}</h4>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{ $slot }}
        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
