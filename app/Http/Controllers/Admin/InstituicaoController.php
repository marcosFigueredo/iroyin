<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instituicao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InstituicaoController extends Controller
{
    public function edit(): View
    {
        return view('admin.instituicao.edit', [
            'inst' => Instituicao::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nome'         => ['required', 'string', 'max:255'],
            'sigla'        => ['required', 'string', 'max:20'],
            'departamento' => ['required', 'string', 'max:255'],
            'cidade'       => ['required', 'string', 'max:100'],
            'estado'       => ['required', 'string', 'max:100'],
            'texto_banner' => ['required', 'string', 'max:100'],
            'logo'         => ['nullable', 'image', 'max:2048'],
            'remover_logo' => ['nullable', 'boolean'],
        ]);

        $inst = Instituicao::current();

        if ($request->hasFile('logo')) {
            $this->deletarLogoAnterior($inst->logo_url);
            $data['logo_url'] = $this->salvarLogo($request->file('logo'), $inst->sigla);
        } elseif ($request->boolean('remover_logo')) {
            $this->deletarLogoAnterior($inst->logo_url);
            $data['logo_url'] = null;
        }

        unset($data['logo'], $data['remover_logo']);
        $inst->update($data);

        return back()->with('success', 'Dados da instituição atualizados com sucesso.');
    }

    private function salvarLogo(\Illuminate\Http\UploadedFile $file, string $sigla): string
    {
        $dir  = public_path('images/logos');
        if (! is_dir($dir)) mkdir($dir, 0755, true);

        $nome = Str::slug($sigla) . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $nome);

        return 'images/logos/' . $nome;
    }

    private function deletarLogoAnterior(?string $logoUrl): void
    {
        if (! $logoUrl) return;
        $path = public_path($logoUrl);
        if (file_exists($path)) unlink($path);
    }
}
