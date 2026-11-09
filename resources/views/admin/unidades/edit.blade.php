<x-app-layout>
    <x-slot name="header">Editar cadastro</x-slot>
    @php($tipos = ['curso' => 'Curso', 'laboratorio' => 'Laboratório', 'centro' => 'Centro de pesquisa'])
    <div class="row justify-content-center"><div class="col-lg-6">
        <div class="card border-0 shadow-sm"><div class="card-body p-4">
            <form method="POST" action="{{ route('admin.unidades.update', $unidade) }}">
                @csrf @method('PUT')
                <div class="mb-3"><label class="form-label fw-semibold">Tipo</label>
                    <select name="tipo" class="form-select">
                        @foreach($tipos as $valor => $rotulo)
                            <option value="{{ $valor }}" @selected(old('tipo', $unidade->tipo) === $valor)>{{ $rotulo }}</option>
                        @endforeach
                    </select></div>
                <div class="mb-3"><label class="form-label fw-semibold">Nome</label>
                    <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome', $unidade->nome) }}" required>
                    @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="ativo" id="ativo" value="1" @checked(old('ativo', $unidade->ativo))>
                    <label class="form-check-label fw-semibold" for="ativo">Ativo</label></div>
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.unidades.index') }}" class="btn btn-outline-secondary">Voltar</a>
                    <button class="btn text-white" style="background-color:#1a3369;">Salvar</button>
                </div>
            </form>
        </div></div>
    </div></div>
</x-app-layout>
