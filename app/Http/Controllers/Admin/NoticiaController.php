<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feed;
use App\Models\Noticia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class NoticiaController extends Controller
{

    public function index()
    {
        foreach (Noticia::where('fim', '<', now())->get() as $expirada) {
            $expirada->removerImagem();
            $expirada->delete();
        }

        $noticias       = Noticia::orderByDesc('inicio')->get()->append('status');
        $feedsGerenciar = Feed::orderBy('nome')->get();
        $feeds          = $feedsGerenciar->where('ativo', true)->map(fn($f) => ['url' => $f->url, 'nome' => $f->nome])->values()->toArray();

        return view('admin.noticias.index', compact('noticias', 'feeds', 'feedsGerenciar'));
    }

    public function create()
    {
        return view('admin.noticias.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo'     => ['required', 'string', 'max:255'],
            'link'       => ['nullable', 'url', 'max:500'],
            'fonte'      => ['required', 'string', 'max:80'],
            'inicio'     => ['required', 'date'],
            'fim'        => ['required', 'date', 'after:inicio'],
            'imagem'     => ['nullable', 'image', 'max:4096'],
            'imagem_url' => ['nullable', 'url', 'max:500'],
        ]);

        // fim enviado como date (YYYY-MM-DD) → expira ao fim do dia
        if (!empty($data['fim']) && \strlen($data['fim']) === 10) {
            $data['fim'] .= ' 23:59:59';
        }

        if ($request->hasFile('imagem')) {
            $data['imagem'] = $this->salvarImagem($request->file('imagem'));
        } elseif (!empty($data['imagem_url'])) {
            $baixada = $this->downloadImagem($data['imagem_url']);
            if ($baixada) $data['imagem'] = $baixada;
        }

        unset($data['imagem_url']);
        $noticia = Noticia::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'noticia' => [
                    'id'     => $noticia->id,
                    'titulo' => $noticia->titulo,
                    'fonte'  => $noticia->fonte,
                    'link'   => $noticia->link,
                    'inicio' => $noticia->inicio->format('d/m H:i'),
                    'fim'    => $noticia->fim->format('d/m H:i'),
                    'status' => $noticia->status,
                ],
            ]);
        }

        return redirect()->route('admin.noticias.index')
            ->with('success', 'Notícia cadastrada com sucesso.');
    }

    public function edit(Noticia $noticia)
    {
        return view('admin.noticias.edit', compact('noticia'));
    }

    public function update(Request $request, Noticia $noticia)
    {
        $data = $request->validate([
            'titulo'     => ['required', 'string', 'max:255'],
            'link'       => ['nullable', 'url', 'max:500'],
            'fonte'      => ['required', 'string', 'max:80'],
            'inicio'     => ['required', 'date'],
            'fim'        => ['required', 'date', 'after:inicio'],
            'imagem'     => ['nullable', 'image', 'max:4096'],
            'imagem_url' => ['nullable', 'url', 'max:500'],
        ]);

        if ($request->hasFile('imagem')) {
            $noticia->removerImagem();
            $data['imagem'] = $this->salvarImagem($request->file('imagem'));
        } elseif (!empty($data['imagem_url'])) {
            $noticia->removerImagem();
            $baixada = $this->downloadImagem($data['imagem_url']);
            if ($baixada) $data['imagem'] = $baixada;
        } else {
            unset($data['imagem']);
        }

        unset($data['imagem_url']);
        $noticia->update($data);

        return redirect()->route('admin.noticias.index')
            ->with('success', 'Notícia atualizada com sucesso.');
    }

    public function destroy(Noticia $noticia)
    {
        $noticia->removerImagem();
        $noticia->delete();
        return back()->with('success', 'Notícia removida.');
    }

    /** AJAX: busca um único feed RSS */
    public function buscarFeed(Request $request): JsonResponse
    {
        $url = $request->input('url');
        if (!$url) {
            return response()->json(['erro' => 'URL não informada.'], 422);
        }

        $content = $this->fetchUrl($url);
        if (!$content) {
            return response()->json(['erro' => 'Não foi possível acessar o feed.'], 422);
        }

        $items = $this->parseFeed($content, null, 10, null);
        return response()->json(['items' => $items]);
    }

    /** AJAX: busca TODOS os feeds ativos em paralelo — filtra <7 dias, ordena com imagem primeiro */
    public function buscarTodos(): JsonResponse
    {
        $feeds   = Feed::ativos()->orderBy('nome')->get(['nome', 'url']);
        $urls    = $feeds->pluck('url')->toArray();
        $nomes   = $feeds->pluck('nome')->toArray();
        $limite  = new \DateTime('-7 days');

        $contents = $this->fetchUrlsParallel($urls);

        $comImagem    = [];
        $semImagem    = [];

        foreach ($contents as $i => $content) {
            if (!$content) continue;
            $fonte = $nomes[$i];
            $parsed = $this->parseFeed($content, $fonte, 3, $limite);
            foreach ($parsed as $item) {
                if ($item['tem_imagem']) {
                    $comImagem[] = $item;
                } else {
                    $semImagem[] = $item;
                }
            }
        }

        // Com imagem primeiro, depois sem
        $items = array_merge($comImagem, $semImagem);

        return response()->json([
            'items'      => \array_slice($items, 0, 60),
            'com_imagem' => \count($comImagem),
            'sem_imagem' => \count($semImagem),
        ]);
    }

    // ── Privados ─────────────────────────────────────────────────────────────

    private function parseFeed(string $content, ?string $fonteFixa, int $limite, ?\DateTime $dataMin): array
    {
        $xml = @simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOERROR | LIBXML_NOWARNING);
        if (!$xml) return [];

        $entries = $xml->channel->item ?? $xml->entry ?? [];
        $items   = [];

        foreach ($entries as $entry) {
            $titulo = trim(strip_tags((string)($entry->title ?? '')));
            $link   = trim((string)($entry->link ?? $entry->id ?? ''));
            $data   = trim((string)($entry->pubDate ?? $entry->published ?? ''));
            $fonte  = $fonteFixa ?? trim(strip_tags((string)($entry->source ?? '')));

            if (!$titulo || !$link) continue;

            $dataFmt = '';
            if ($data) {
                try {
                    $dt = new \DateTime($data);
                    if ($dataMin && $dt < $dataMin) continue;
                    $dataFmt = $dt->format('d/m/Y');
                } catch (\Exception) {}
            }

            // Imagem no RSS — tenta múltiplas origens em ordem de preferência
            $imgUrl = '';

            // 1) media:content / media:thumbnail (feeds Atom/RSS com namespace)
            $entry->registerXPathNamespace('media', 'http://search.yahoo.com/mrss/');
            foreach ($entry->xpath('media:content') as $mc) {
                if (!empty($mc['url'])) { $imgUrl = (string)$mc['url']; break; }
            }
            if (!$imgUrl) {
                foreach ($entry->xpath('media:thumbnail') as $mt) {
                    if (!empty($mt['url'])) { $imgUrl = (string)$mt['url']; break; }
                }
            }

            // 2) enclosure com tipo image/*
            if (!$imgUrl && isset($entry->enclosure)) {
                $type = (string)$entry->enclosure['type'];
                if (str_starts_with($type, 'image/')) {
                    $imgUrl = (string)$entry->enclosure['url'];
                }
            }

            // 3) <img> embutido no <description> — padrão dos feeds do Google News
            if (!$imgUrl && isset($entry->description)) {
                $desc = html_entity_decode((string)$entry->description, ENT_QUOTES | ENT_HTML5);
                if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $desc, $m)) {
                    $imgUrl = $m[1];
                }
            }

            $items[] = [
                'titulo'     => $titulo,
                'link'       => $link,
                'fonte'      => $fonte ?: 'RSS',
                'data'       => $dataFmt,
                'imagem_url' => $imgUrl,
                'tem_imagem' => !empty($imgUrl),
            ];

            if (\count($items) >= $limite) break;
        }

        return $items;
    }

    /** Busca múltiplas URLs em paralelo via curl_multi */
    private function fetchUrlsParallel(array $urls): array
    {
        if (!\function_exists('curl_multi_init')) {
            // Fallback sequencial se curl não disponível
            return array_map(fn($u) => $this->fetchUrl($u), $urls);
        }

        $mh      = curl_multi_init();
        $handles = [];

        foreach ($urls as $i => $url) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 8,
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (IROYIN)',
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 3,
            ]);
            curl_multi_add_handle($mh, $ch);
            $handles[$i] = $ch;
        }

        $running = null;
        do {
            curl_multi_exec($mh, $running);
            curl_multi_select($mh, 0.1);
        } while ($running > 0);

        $results = [];
        foreach ($handles as $i => $ch) {
            $results[$i] = curl_multi_getcontent($ch) ?: '';
            curl_multi_remove_handle($mh, $ch);
        }
        curl_multi_close($mh);

        return $results;
    }

    private function fetchUrl(string $url): string
    {
        $ctx = stream_context_create(['http' => [
            'user_agent' => 'Mozilla/5.0 (IROYIN)',
            'timeout'    => 8,
        ]]);
        return (string)@file_get_contents($url, false, $ctx);
    }

    /** Baixa imagem de URL externa e salva em public/noticias/imagens/ */
    private function downloadImagem(string $url): ?string
    {
        try {
            $ctx     = stream_context_create(['http' => [
                'user_agent' => 'Mozilla/5.0 (IROYIN)',
                'timeout'    => 10,
            ]]);
            $content = @file_get_contents($url, false, $ctx);
            if (!$content) return null;

            $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
            if (!\in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) $ext = 'jpg';

            $nome = time() . '_feed.' . $ext;
            file_put_contents(public_path('noticias/imagens/' . $nome), $content);
            return 'noticias/imagens/' . $nome;
        } catch (\Exception) {
            return null;
        }
    }

    private function salvarImagem(UploadedFile $file): string
    {
        $nome = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
              . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('noticias/imagens'), $nome);
        return 'noticias/imagens/' . $nome;
    }
}
