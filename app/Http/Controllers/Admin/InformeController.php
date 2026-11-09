<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Informe;
use App\Models\Instituicao;
use App\Support\ImagemUpload;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InformeController extends Controller
{
    public function index(Request $request)
    {
        if ($this->semEscopo($request)) {
            return $this->pedirDepartamento();
        }

        return view('admin.informes.index', [
            'informes' => $this->visiveis($request)->with(['departamento', 'unidade'])->latest()->paginate(30),
        ]);
    }

    public function create(Request $request)
    {
        if ($this->semEscopo($request)) {
            return $this->pedirDepartamento();
        }

        return view('admin.informes.create', ['informe' => new Informe(['urgencia' => 'normal', 'formato' => 'texto'])] + $this->contexto($request));
    }

    public function store(Request $request)
    {
        if ($this->semEscopo($request)) {
            return $this->pedirDepartamento();
        }

        $data = $this->validar($request);
        $usuario = $request->user();

        if ($data['formato'] === 'imagem' && $request->hasFile('imagem')) {
            $data['imagem'] = ImagemUpload::salvar($request->file('imagem'));
        }

        Informe::create($data + [
            'user_id'         => $usuario->id,
            'departamento_id' => $usuario->isReitoria() ? null : $usuario->escopoDepartamentoId(),
            'unidade_id'      => $usuario->isUnidade() ? $usuario->unidade_id : null,
        ]);

        return redirect()->route('admin.informes.index')->with('success', 'Informe publicado.');
    }

    public function edit(Request $request, int $informe)
    {
        if ($this->semEscopo($request)) {
            return $this->pedirDepartamento();
        }

        return view('admin.informes.edit', ['informe' => $this->buscar($request, $informe)] + $this->contexto($request));
    }

    public function update(Request $request, int $informe)
    {
        if ($this->semEscopo($request)) {
            return $this->pedirDepartamento();
        }

        $item = $this->buscar($request, $informe);
        $data = $this->validar($request, $item);

        if ($data['formato'] !== 'imagem') {
            ImagemUpload::apagar($item->imagem);
            $data['imagem'] = null;
        } elseif ($request->hasFile('imagem')) {
            ImagemUpload::apagar($item->imagem);
            $data['imagem'] = ImagemUpload::salvar($request->file('imagem'));
        }

        $item->update($data);

        return redirect()->route('admin.informes.index')->with('success', 'Informe atualizado.');
    }

    public function destroy(Request $request, int $informe)
    {
        if ($this->semEscopo($request)) {
            return $this->pedirDepartamento();
        }

        $item = $this->buscar($request, $informe);
        ImagemUpload::apagar($item->imagem);
        $item->delete();

        return back()->with('success', 'Informe removido.');
    }

    private function semEscopo(Request $request): bool
    {
        return $request->user()->isGestor() && ! $request->user()->escopoDepartamentoId();
    }

    private function pedirDepartamento()
    {
        return redirect()->route('admin.departamentos.index')
            ->with('error', 'Escolha um departamento em "Operar como" para continuar.');
    }

    private function visiveis(Request $request): Builder
    {
        $usuario = $request->user();

        return match (true) {
            $usuario->isReitoria() => Informe::whereNull('departamento_id'),
            $usuario->isUnidade()  => Informe::where('unidade_id', $usuario->unidade_id),
            default                => Informe::where('departamento_id', $usuario->escopoDepartamentoId()),
        };
    }

    private function buscar(Request $request, int $id): Informe
    {
        return $this->visiveis($request)->findOrFail($id);
    }

    private function contexto(Request $request): array
    {
        $usuario = $request->user();
        $departamento = Departamento::find($usuario->escopoDepartamentoId());

        return [
            'podeImagem' => ! $usuario->isUnidade(),
            'logoUrl'    => ($logo = Instituicao::current()->logo_url) ? asset($logo) : asset('images/logoUneb.png'),
            'tema'       => $departamento?->tema ?? 'azul',
            'cabecalho'  => $departamento
                ? mb_strtoupper($departamento->slug.' · '.$departamento->nome)
                : 'REITORIA',
            'origem'     => match (true) {
                $usuario->isReitoria() => 'Reitoria',
                $usuario->isUnidade()  => $usuario->unidade->nome.' · '.mb_strtoupper($departamento->slug),
                default                => mb_strtoupper($departamento->slug),
            },
        ];
    }

    private function validar(Request $request, ?Informe $atual = null): array
    {
        $formatos = $request->user()->isUnidade() ? ['texto'] : ['texto', 'imagem'];

        $data = $request->validate([
            'formato'  => ['required', Rule::in($formatos)],
            'titulo'   => ['required', 'string', 'max:70'],
            'texto'    => ['nullable', 'required_if:formato,texto', 'string', 'max:160'],
            'imagem'   => [
                Rule::requiredIf($request->input('formato') === 'imagem' && ! $atual?->imagem),
                'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096',
            ],
            'urgencia' => ['required', Rule::in(Informe::URGENCIAS)],
            'inicio'   => ['required', 'date'],
            'fim'      => ['required', 'date', 'after_or_equal:inicio'],
        ], [
            'formato.in' => 'Seu perfil publica apenas informes de texto.',
        ]);

        $inicio = Carbon::parse($data['inicio'])->startOfDay();
        $fim = Carbon::parse($data['fim'])->startOfDay();

        if ($data['urgencia'] === 'urgente' && $inicio->diffInDays($fim) > Informe::MAX_DIAS_URGENTE) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'fim' => 'Um informe urgente pode durar no máximo '.Informe::MAX_DIAS_URGENTE.' dias.',
            ]);
        }

        $data['inicio'] = $inicio;
        $data['fim'] = $fim->endOfDay();
        unset($data['imagem']);

        return $data;
    }
}
