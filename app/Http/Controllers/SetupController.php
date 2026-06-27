<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SetupController extends Controller
{
    public function index()
    {
        if (User::count() > 0) {
            return redirect()->route('login');
        }

        return view('setup');
    }

    public function store(Request $request)
    {
        if (User::count() > 0) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'admin',
            'active'   => true,
        ]);

        $this->seedDefaultFeeds();

        Auth::login($user);

        return redirect()->route('admin.instituicao.edit')
            ->with('success', 'Conta criada com sucesso! Configure agora os dados da sua instituição.');
    }

    private function seedDefaultFeeds(): void
    {
        $feeds = [
            ['nome' => 'CAPES',          'url' => 'https://www.gov.br/capes/pt-br/assuntos/noticias/RSS'],
            ['nome' => 'CNPq',           'url' => 'https://www.gov.br/cnpq/pt-br/assuntos/noticias/RSS'],
            ['nome' => 'MCTI',           'url' => 'https://www.gov.br/mcti/pt-br/assuntos/noticias/RSS'],
            ['nome' => 'FINEP',          'url' => 'https://www.gov.br/finep/pt-br/assuntos/noticias/RSS'],
            ['nome' => 'INPE',           'url' => 'https://www.gov.br/inpe/pt-br/assuntos/ultimas-noticias/RSS'],
            ['nome' => 'MEC',            'url' => 'https://www.gov.br/mec/pt-br/assuntos/noticias/RSS'],
            ['nome' => 'SBC',            'url' => 'https://www.sbc.org.br/feed/'],
            ['nome' => 'IMPA',           'url' => 'https://impa.br/feed/'],
            ['nome' => 'SBPC',           'url' => 'https://www.sbpcnet.org.br/site/feed/'],
            ['nome' => 'SBMAC',          'url' => 'https://www.sbmac.org.br/feed/'],
            ['nome' => 'IEEE Spectrum',  'url' => 'https://spectrum.ieee.org/feeds/feed.rss'],
            ['nome' => 'Agência FAPESP', 'url' => 'https://agencia.fapesp.br/feed/'],
        ];

        foreach ($feeds as $feed) {
            Feed::firstOrCreate(['url' => $feed['url']], ['nome' => $feed['nome'], 'ativo' => true]);
        }
    }
}
