<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    <div class="row g-4">
        @if(auth()->user()->isAdmin())
        <div class="col-sm-4">
            <a href="{{ route('admin.users.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">Usuários cadastrados</div>
                        <div class="fw-bold fs-2" style="color:#1a3369;">{{ \App\Models\User::count() }}</div>
                    </div>
                </div>
            </a>
        </div>
        @endif

        <div class="col-sm-4">
            <a href="{{ route('admin.semestres.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">Horários</div>
                        <div class="fw-bold fs-2" style="color:#1a3369;">{{ \App\Models\Semestre::count() }}</div>
                        <div class="text-muted small mt-1">semestre(s) cadastrado(s)</div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Notícias publicadas</div>
                    <div class="fw-bold fs-2" style="color:#1a3369;">—</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
