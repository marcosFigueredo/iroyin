<x-app-layout>
    <x-slot name="header">Usuários</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <p class="text-muted mb-0">Gerencie os usuários com acesso ao sistema.</p>
        <a href="{{ route('admin.users.create') }}" class="btn text-white" style="background-color:#1a3369;">
            <i class="bi bi-person-plus-fill me-1"></i> Novo usuário
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Perfil</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="fw-semibold">
                            {{ $user->name }}
                            @if($user->id === auth()->id())
                                <span class="badge bg-secondary ms-1" style="font-size:10px;">você</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $user->email }}</td>
                        <td>
                            @if($user->role === 'admin')
                                <span class="badge" style="background-color:#1a3369;">Admin</span>
                            @else
                                <span class="badge bg-secondary">Editor</span>
                            @endif
                        </td>
                        <td>
                            @if($user->active)
                                <span class="badge bg-success">Ativo</span>
                            @else
                                <span class="badge bg-danger">Inativo</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="btn btn-sm btn-outline-secondary me-1">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Excluir {{ $user->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Nenhum usuário cadastrado.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
