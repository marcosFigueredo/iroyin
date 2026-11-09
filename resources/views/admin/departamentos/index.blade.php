<x-app-layout>
    <x-slot name="header">Departamentos</x-slot>
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm"><div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:#1a3369;">Novo departamento</h6>
                <form method="POST" action="{{ route('admin.departamentos.store') }}">
                    @csrf
                    <div class="mb-3"><label class="form-label fw-semibold">Campus</label>
                        <select name="campus_id" class="form-select @error('campus_id') is-invalid @enderror" required>
                            @foreach($campi as $campus)
                                <option value="{{ $campus->id }}" @selected(old('campus_id') == $campus->id)>{{ $campus->nome }}</option>
                            @endforeach
                        </select>
                        @error('campus_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="mb-3"><label class="form-label fw-semibold">Sigla</label>
                        <input type="text" name="sigla" class="form-control @error('sigla') is-invalid @enderror" value="{{ old('sigla') }}" required>
                        @error('sigla')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="mb-3"><label class="form-label fw-semibold">Nome</label>
                        <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome') }}" required>
                        @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="mb-3"><label class="form-label fw-semibold">Endereço do monitor</label>
                        <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="Gerado automaticamente">
                        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Sigla + número do campus, como dcet2.</div></div>
                    <button class="btn text-white w-100" style="background-color:#1a3369;">Criar</button>
                </form>
            </div></div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm"><div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Endereço</th><th>Departamento</th><th>Campus</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse($departamentos as $dep)
                        <tr>
                            <td><a href="{{ url('/'.$dep->slug) }}" target="_blank">/{{ $dep->slug }}</a></td>
                            <td class="fw-semibold">{{ $dep->nome }}</td>
                            <td class="text-muted">{{ $dep->campus->nome }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.departamentos.update', $dep) }}">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="campus_id" value="{{ $dep->campus_id }}">
                                    <input type="hidden" name="sigla" value="{{ $dep->sigla }}">
                                    <input type="hidden" name="nome" value="{{ $dep->nome }}">
                                    <input type="hidden" name="slug" value="{{ $dep->slug }}">
                                    @unless($dep->ativo)<input type="hidden" name="ativo" value="1">@endunless
                                    <button class="btn btn-sm {{ $dep->ativo ? 'btn-outline-success' : 'btn-outline-secondary' }}">{{ $dep->ativo ? 'Ativo' : 'Inativo' }}</button>
                                </form>
                            </td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.departamentos.destroy', $dep) }}" class="d-inline" onsubmit="return confirm('Excluir este departamento?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Excluir"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Nenhum departamento cadastrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div></div>
        </div>
    </div>
</x-app-layout>
