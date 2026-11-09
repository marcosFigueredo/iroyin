<x-app-layout>
    <x-slot name="header">Informes</x-slot>

    <div class="d-flex justify-content-end mb-4">
        <a href="{{ route('admin.informes.create') }}" class="btn text-white" style="background-color:#1a3369;">
            <i class="bi bi-plus-lg me-1"></i> Novo informe
        </a>
    </div>

    <div class="card border-0 shadow-sm"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Título</th><th>Origem</th><th>Urgência</th><th>Período</th><th></th></tr></thead>
            <tbody>
                @forelse($informes as $informe)
                <tr>
                    <td class="fw-semibold">{{ $informe->titulo }}</td>
                    <td class="text-muted">{{ $informe->origem() }}</td>
                    <td>
                        <span class="badge {{ ['urgente' => 'bg-danger', 'importante' => 'bg-warning text-dark'][$informe->urgencia] ?? 'bg-secondary' }}">{{ ucfirst($informe->urgencia) }}</span>
                    </td>
                    <td class="text-muted small">{{ $informe->inicio->format('d/m/Y') }} a {{ $informe->fim->format('d/m/Y') }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.informes.edit', $informe) }}" class="btn btn-sm btn-outline-secondary me-1"><i class="bi bi-pencil"></i> Editar</a>
                        <form method="POST" action="{{ route('admin.informes.destroy', $informe) }}" class="d-inline" onsubmit="return confirm('Excluir este informe?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Excluir"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Nenhum informe publicado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div></div>

    <div class="mt-3">{{ $informes->links() }}</div>
</x-app-layout>
