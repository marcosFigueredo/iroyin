<?php

namespace Database\Seeders;

use App\Models\Agencia;
use App\Models\Edital;
use App\Models\Feed;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // O administrador inicial é criado pelo wizard de setup (/setup).
        // Este seeder popula feeds e agências padrão (seguro para re-execução).

        $feeds = [
            ['nome' => 'CAPES',          'url' => 'https://www.gov.br/capes/pt-br/assuntos/noticias/RSS'],
            ['nome' => 'CNPq',           'url' => 'https://www.gov.br/cnpq/pt-br/assuntos/noticias/RSS'],
            ['nome' => 'FAPESB',         'url' => 'https://www.fapesb.ba.gov.br/feed/'],
            ['nome' => 'MCTI',           'url' => 'https://www.gov.br/mcti/pt-br/assuntos/noticias/RSS'],
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

        // Agências de fomento pré-cadastradas
        $agencias = [
            [
                'nome'            => 'Coordenação de Aperfeiçoamento de Pessoal de Nível Superior',
                'sigla'           => 'CAPES',
                'cor_hex'         => '#003366',
                'url_noticias_rss'=> 'https://www.gov.br/capes/pt-br/assuntos/noticias/RSS',
                'url_editais'     => 'https://www.gov.br/capes/pt-br/assuntos/editais',
            ],
            [
                'nome'            => 'Conselho Nacional de Desenvolvimento Científico e Tecnológico',
                'sigla'           => 'CNPq',
                'cor_hex'         => '#1a5276',
                'url_noticias_rss'=> 'https://www.gov.br/cnpq/pt-br/assuntos/noticias/RSS',
                'url_editais'     => 'https://www.gov.br/cnpq/pt-br/assuntos/oportunidades/bolsas-e-auxilios',
            ],
            [
                'nome'            => 'Financiadora de Estudos e Projetos',
                'sigla'           => 'FINEP',
                'cor_hex'         => '#c0392b',
                'url_noticias_rss'=> null,
                'url_editais'     => 'https://www.finep.gov.br/oportunidades',
            ],
            [
                'nome'            => 'Fundação de Amparo à Pesquisa do Estado da Bahia',
                'sigla'           => 'FAPESB',
                'cor_hex'         => '#1e8449',
                'url_noticias_rss'=> 'https://www.fapesb.ba.gov.br/feed/',
                'url_editais'     => 'https://www.fapesb.ba.gov.br/editais/',
            ],
        ];

        $agenciaIds = [];
        foreach ($agencias as $ag) {
            $obj = Agencia::firstOrCreate(['sigla' => $ag['sigla']], array_merge($ag, ['ativo' => true]));
            $agenciaIds[$ag['sigla']] = $obj->id;
        }

        // Editais de demonstração (CNPq — único que disponibiliza dados estruturados)
        $editaisSample = [
            [
                'agencia_sigla'   => 'CNPq',
                'titulo'          => 'Chamada CNPq/MCTI nº 10/2026 — Pesquisa Básica e Aplicada',
                'objetivo'        => 'Apoiar projetos de pesquisa básica e aplicada em todas as áreas do conhecimento, com ênfase em ciência, tecnologia e inovação.',
                'link'            => 'https://www.gov.br/cnpq/pt-br/assuntos/oportunidades/bolsas-e-auxilios',
                'data_fechamento' => now()->addDays(45)->toDateString(),
            ],
            [
                'agencia_sigla'   => 'CNPq',
                'titulo'          => 'Chamada Universal CNPq 2026 — Grupos Consolidados',
                'objetivo'        => 'Financiamento de projetos de pesquisa de grupos consolidados para fomento ao desenvolvimento científico nacional.',
                'link'            => 'https://www.gov.br/cnpq/pt-br/assuntos/oportunidades/bolsas-e-auxilios',
                'data_fechamento' => now()->addDays(20)->toDateString(),
            ],
            [
                'agencia_sigla'   => 'CAPES',
                'titulo'          => 'Edital CAPES/PrInt 2026 — Internacionalização',
                'objetivo'        => 'Fomento à internacionalização da pós-graduação brasileira por meio de missões de pesquisa e visitas técnicas.',
                'link'            => 'https://www.gov.br/capes/pt-br/assuntos/editais',
                'data_fechamento' => now()->addDays(60)->toDateString(),
            ],
        ];

        foreach ($editaisSample as $ed) {
            $agId = $agenciaIds[$ed['agencia_sigla']] ?? null;
            Edital::firstOrCreate(
                ['titulo' => $ed['titulo']],
                [
                    'agencia_id'      => $agId,
                    'objetivo'        => $ed['objetivo'],
                    'link'            => $ed['link'],
                    'data_fechamento' => $ed['data_fechamento'],
                    'ativo'           => true,
                ]
            );
        }
    }
}
