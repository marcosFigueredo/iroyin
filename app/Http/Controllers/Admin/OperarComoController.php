<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OperarComoController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate(['departamento_id' => ['nullable', 'exists:departamentos,id']]);

        if (empty($data['departamento_id'])) {
            session()->forget('operar_departamento_id');
        } else {
            session(['operar_departamento_id' => (int) $data['departamento_id']]);
        }

        return back();
    }
}
