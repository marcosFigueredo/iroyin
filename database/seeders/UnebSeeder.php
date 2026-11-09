<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Departamento;
use Illuminate\Database\Seeder;

/**
 * Campi e departamentos da UNEB. Opcional: outra instituição não deve rodar.
 * Fonte: https://portal.uneb.br/multicampia-e-departamentos/ (consultada em 2026-11-09).
 *
 *   php artisan db:seed --class=UnebSeeder
 */
class UnebSeeder extends Seeder
{
    private const NOMES = [
        'DCH'       => 'Departamento de Ciências Humanas',
        'DEDC'      => 'Departamento de Educação',
        'DCET'      => 'Departamento de Ciências Exatas e da Terra',
        'DCV'       => 'Departamento de Ciências da Vida',
        'DLLARTES'  => 'Departamento de Linguística, Literatura e Artes',
        'DTCS'      => 'Departamento de Tecnologia e Ciências Sociais',
        'DCHT'      => 'Departamento de Ciências Humanas e Tecnologias',
        'DMCE'      => 'Departamento Multidisciplinar de Ciências e Educação',
        'DCT/OPARÁ' => 'Departamento dos Povos Indígenas, Comunidades Tradicionais e Camponesas',
    ];

    /** [número do campus, cidade, siglas] */
    private const CAMPI = [
        [1, 'Salvador', ['DCH', 'DEDC', 'DCET', 'DCV']],
        [2, 'Alagoinhas', ['DEDC', 'DCET', 'DLLARTES']],
        [3, 'Juazeiro', ['DTCS', 'DCH']],
        [4, 'Jacobina', ['DCH']],
        [5, 'Santo Antônio de Jesus', ['DCH']],
        [6, 'Caetité', ['DCH']],
        [7, 'Senhor do Bonfim', ['DEDC']],
        [8, 'Paulo Afonso', ['DEDC']],
        [9, 'Barreiras', ['DCH']],
        [10, 'Teixeira de Freitas', ['DEDC']],
        [11, 'Serrinha', ['DEDC']],
        [12, 'Guanambi', ['DEDC']],
        [13, 'Itaberaba', ['DEDC']],
        [14, 'Conceição do Coité', ['DEDC']],
        [15, 'Valença', ['DEDC']],
        [16, 'Irecê', ['DCHT']],
        [17, 'Bom Jesus da Lapa', ['DCHT']],
        [18, 'Eunápolis', ['DCHT']],
        [19, 'Camaçari', ['DCHT']],
        [20, 'Brumado', ['DCHT']],
        [21, 'Ipiaú', ['DCHT']],
        [22, 'Euclides da Cunha', ['DCHT']],
        [23, 'Seabra', ['DCHT']],
        [24, 'Xique-Xique', ['DCHT']],
        [null, 'Lauro de Freitas', ['DMCE']],
        [null, 'Jeremoabo', ['DCT/OPARÁ']],
    ];

    private const SLUGS_ESPECIAIS = ['DCT/OPARÁ' => 'opara'];

    public function run(): void
    {
        foreach (self::CAMPI as [$numero, $cidade, $siglas]) {
            $campus = Campus::updateOrCreate(
                ['nome' => $cidade],
                ['numero' => $numero, 'cidade' => $cidade, 'estado' => 'BA'],
            );

            foreach ($siglas as $sigla) {
                Departamento::updateOrCreate(
                    ['slug' => self::SLUGS_ESPECIAIS[$sigla] ?? Departamento::gerarSlug($sigla, $numero)],
                    ['campus_id' => $campus->id, 'sigla' => $sigla, 'nome' => self::NOMES[$sigla]],
                );
            }
        }
    }
}
