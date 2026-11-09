<x-app-layout>
    <x-slot name="header">Novo Usuário</x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Nome</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">E-mail</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label fw-semibold">Perfil</label>
                            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                @foreach($perfis as $valor => $rotulo)
                                    <option value="{{ $valor }}" @selected(old('role', $user->role ?? 'unidade') === $valor)>{{ $rotulo }}</option>
                                @endforeach
                            </select>
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        @if(auth()->user()->isGestor())
                        <div class="mb-3">
                            <label for="departamento_id" class="form-label fw-semibold">Departamento</label>
                            <select class="form-select @error('departamento_id') is-invalid @enderror" id="departamento_id" name="departamento_id">
                                <option value="">—</option>
                                @foreach($departamentos as $dep)
                                    <option value="{{ $dep->id }}" @selected(old('departamento_id', $user->departamento_id ?? null) == $dep->id)>{{ strtoupper($dep->slug) }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Para o perfil Gabinete do departamento.</div>
                            @error('departamento_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        @endif

                        <div class="mb-3">
                            <label for="unidade_id" class="form-label fw-semibold">Curso ou unidade</label>
                            <select class="form-select @error('unidade_id') is-invalid @enderror" id="unidade_id" name="unidade_id">
                                <option value="">—</option>
                                @foreach($unidades as $un)
                                    <option value="{{ $un->id }}" @selected(old('unidade_id', $user->unidade_id ?? null) == $un->id)>{{ $un->nome }} · {{ strtoupper($un->departamento->slug) }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Para o perfil Secretaria.</div>
                            @error('unidade_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Senha</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" required>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">Confirmar Senha</label>
                            <input type="password" class="form-control"
                                   id="password_confirmation" name="password_confirmation" required>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Voltar
                            </a>
                            <button type="submit" class="btn text-white" style="background-color:#1a3369;">
                                <i class="bi bi-check-lg me-1"></i> Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
