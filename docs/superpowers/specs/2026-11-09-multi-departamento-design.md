# IROYIN multi-departamento: desenho

Data: 2026-11-09. Estado: aguardando aprovação de Marcos Figueredo.

Legenda: **[decidido]** veio do Marcos. **[proposta]** foi proposto na conversa e aceito sem objeção. **[decisão do autor]** é escolha de quem escreveu este documento, ainda não discutida; corrija se estiver errada.

## 1. Por que

Vários departamentos da UNEB pediram para usar o IROYIN. Hoje o app atende um único departamento: aulas não têm vínculo com departamento, há um semestre ativo, uma configuração de nome e tema, e o mesmo endereço de monitor para todos. Se outro departamento cadastrasse aulas, elas apareceriam em todas as telas.

A mudança precisa acontecer antes do primeiro deploy em `iroyin.ppgmsb.uneb.br`, porque altera o banco, os logins e o endereço dos monitores.

O app segue aberto para qualquer instituição (é tema de publicação acadêmica), com foco inicial na UNEB. **[decidido]**

## 2. Objetivos

1. Um único sistema e um único banco no servidor, com vários departamentos. **[decidido]**
2. Cada monitor mostra **as aulas do seu departamento**, **os informes do seu departamento** e **os informes gerais da Reitoria**. **[decidido; a separação entre informe local e geral é leitura do autor a confirmar]**
3. Cada departamento administra seus cursos, laboratórios e centros de pesquisa. **[decidido]**
4. Cursos informam horários e publicam informes. Laboratórios e centros de pesquisa só publicam informes. **[decidido]**
5. O gestor central cadastra o calendário acadêmico, só com início e fim de cada semestre. **[decidido]**
6. Gabinetes e secretarias publicam informes curtos, com urgência, vendo na hora como vai sair na tela. **[decidido]**
7. Toda mensagem fica na tela o tempo necessário para ser vista por inteiro, e pode ser lida na íntegra e sem tempo no celular. **[decidido]**

## 3. Fora de escopo

- Várias universidades no mesmo banco. Outra instituição instala a própria cópia. **[proposta]**
- Pastas físicas ou uma cópia do app por departamento. A "pasta" é um endereço (`/dcet2`). **[proposta]**
- Cadastro individual de cada monitor, ou monitor filtrado por curso. O monitor é sempre do departamento. **[decidido]**
- Calendário próprio por departamento. **[proposta]**
- Deploy no servidor: é uma etapa separada, feita só quando pedido.

## 4. O que não pode acontecer

Mesmo com tudo "funcionando", o projeto falhou se:

- Um departamento consegue ver ou alterar, pelo painel, cadastros de outro.
- A aula de um departamento aparece no monitor de outro.
- Um aviso local urgente some no meio de avisos de outros campi.
- "Urgente" vira rotina, ou o pulsar incomoda quem passa. O pulsar deve ser discreto. **[decidido]**
- O monitor fica em branco porque uma parte da API falhou. O comportamento atual de degradar sem quebrar deve ser mantido.
- Uma mensagem sai da tela antes de dar tempo de ser lida.

## 5. Estrutura

```
Instituição (uma por instalação)
└── Campus (nome, cidade, número)
    └── Departamento (sigla, nome, endereço/slug, tema, tempos)
        └── Unidade (tipo: curso | laboratório | centro de pesquisa)
```

- `Instituicao` já existe e mantém nome, sigla e logo. O campo `departamento` sai de lá e vira a tabela de departamentos.
- `Configuracao` hoje mistura o que é geral e o que é do departamento:
  - Por departamento: `duracao_horarios`, `duracao_noticia`, `tema`. **[proposta]**
  - Por campus: cidade do clima. **[proposta]**
  - Geral da instalação: `weather_api_key`. **[decisão do autor]**
- Cidade do clima com cache por cidade: o `ClimaController` já faz isso (`clima_<md5 da cidade>`), então departamentos do mesmo campus compartilham o cache e o limite da API fica protegido.
- `Horario` passa a pertencer a uma unidade do tipo curso. O texto livre `curso` deixa de ser digitado e vem do nome da unidade. **[decisão do autor]**
- Unidades do tipo laboratório e centro de pesquisa não podem ter horários. **[decidido]**

## 6. Quem faz o quê

Quatro perfis **[decidido]**:

| Perfil | Quem é | Pode |
|---|---|---|
| Gestor central | Administração do sistema | Campi, departamentos, Reitoria e seus usuários, calendário acadêmico, feeds, agências, notícias e editais comuns, dados da instituição. |
| Reitoria | Gabinete da Reitoria | Publicar informes gerais da universidade, em texto ou imagem, que aparecem em todos os monitores. |
| Departamento | Gabinete do departamento | Tema e tempos do próprio departamento; cadastrar cursos, unidades e seus secretários; publicar informes em texto ou imagem do departamento. |
| Curso/unidade | Secretaria do curso, laboratório ou centro | Dados do próprio curso, horários (só em cursos) e informes Logo + texto da própria unidade. |

- Só a Reitoria publica para todos os monitores. O seletor "Quem vê" sai do formulário: o alcance decorre do perfil. **[decidido; leitura do autor a confirmar]**
- O gestor pode entrar no painel como qualquer departamento, para dar suporte. **[decisão do autor]**
- Um usuário pertence a um único departamento ou unidade. **[decisão do autor]**
- Todo acesso é verificado no servidor. Esconder o botão não basta.

## 7. Calendário acadêmico

- O gestor central cadastra semestres com nome, início e fim. **[decidido]**
- O semestre vigente é o que contém a data de hoje. O botão "ativar" deixa de existir. **[proposta]**
- Dois semestres não podem ter datas sobrepostas. **[decisão do autor]**
- Sem semestre vigente (férias), o monitor segue só com as mensagens, como já faz quando não há aula. **[proposta]**
- Cursos cadastram horários em qualquer semestre, inclusive futuro, para deixar pronto antes do início. **[decisão do autor]**

## 8. Monitor

- Cada departamento tem o endereço `iroyin.ppgmsb.uneb.br/<slug>`, gerado quando o gestor cria o departamento. **[decidido]**
- O Raspberry Pi abre esse endereço. Não há login no monitor. **[proposta]**
- O cabeçalho mostra o nome do departamento do endereço. **[decidido]**
- A acessibilidade acompanha: `/<slug>/acessivel`, e o QR code do monitor aponta para ela. **[decisão do autor]**
- A raiz `/` mostra uma lista simples de departamentos com seus links. **[decisão do autor]**
- Slug desconhecido retorna 404. Um departamento desativado também.
- Slugs não podem coincidir com rotas do sistema (`admin`, `api`, `login`, `setup`, `dashboard`, `profile`, `build`, `assets`, `images` e pastas de `public/`).

### O que mostrar

A ordem do ciclo atual é mantida (horários → banner → mensagens). A escolha do que entra em cada volta segue a seção 9, "Critérios de exibição".

## 9. Informes

Informes são conteúdo da universidade, separado das notícias e editais das agências (CAPES, CNPq etc.), que continuam comuns a todas as telas e sob o gestor central. **[decidido]**

### Formato **[decidido]**

O formato novo a implementar é só o **Logo + texto**: logo da UNEB à esquerda, em área menor (24% da largura), título e texto à direita. Título com até **70** caracteres e texto com até **160**, com limite imposto no formulário e no servidor.

Informes com imagem **reaproveitam o formato já aprovado** das notícias com imagem (upload de até 4 MB, imagem à esquerda e texto à direita no monitor). Não se cria layout novo. Podem publicar por ele o Departamento e a Reitoria. **[decidido]**

### Campos

Título, texto, urgência (normal, importante, urgente), início e fim da exibição, quem publicou. Cada informe mostra quem publicou, por exemplo "Colegiado de Sistemas de Informação · DCET2". O monitor exibe o título, o texto e a origem. **[decidido]** Gabinete do departamento aparece com a sigla do departamento; a Reitoria, como "Reitoria". **[decisão do autor]**

### Urgência **[proposta]**

| Nível | Efeito |
|---|---|
| Normal | Segue a ordem do ciclo. |
| Importante | Aparece primeiro no seu grupo; selo amarelo. |
| Urgente | Fundo vermelho-escuro e contorno vermelho com pulsar lento e discreto (ciclo de 2,6 s). Interrompe o quadro de horários. Depois reaparece a cada volta do ciclo até vencer. |

- Com vários urgentes, os do próprio departamento vêm antes dos da Reitoria, e dentro de cada grupo o mais recente primeiro. Cada interrupção mostra no máximo 3; os demais entram nas voltas seguintes. **[decisão do autor]**
- O monitor consulta urgentes a cada 60 segundos, para a interrupção não depender do fim do ciclo. **[decisão do autor]**
- Secretarias podem marcar "Urgente" dentro do próprio departamento. **[decisão do autor]**
- Informe urgente precisa ter data de fim de no máximo 7 dias. **[decisão do autor]**
- Urgentes válidos aparecem também na tela Boa Noite. **[decisão do autor]**

### Critérios de exibição **[decidido; regras detalhadas são proposta do autor]**

O princípio é: **nenhuma mensagem sai da tela antes de poder ser vista por inteiro.**

- O tempo de cada mensagem é calculado pelo tamanho do texto, em vez de fixo: cerca de 15 palavras a cada 5 segundos, com mínimo de 10 e máximo de 30 segundos. Notícias e editais seguem a mesma regra, com o tempo do departamento como teto.
- Mensagem nunca é cortada. O que não couber na volta atual fica para a próxima.
- Cada bloco de mensagens tem um tempo total limite, para os horários voltarem à tela. Esse limite usa `duracao_noticia` e `duracao_horarios` como base e é ajustado na construção.
- Prioridade para entrar na volta, nesta ordem: urgentes; importantes; informes do departamento; informes da Reitoria; notícias e editais. Dentro de cada grupo, vai primeiro o que apareceu há mais tempo, para todos terem a vez.
- O QR code do monitor leva à versão do celular.

### No celular **[decidido]**

A página de acessibilidade do departamento (`/<slug>/acessivel`, instalável no celular) ganha a lista dos informes vigentes, na íntegra, sem tempo de troca: Reitoria e departamento, urgentes primeiro.

### Painel com pré-visualização **[decidido]**

Formulário à esquerda e monitor em miniatura à direita, atualizando a cada tecla, com contador de caracteres. O protótipo aprovado é a referência visual ([informe-painel-v3.html](2026-11-09-multi-departamento-assets/informe-painel-v3.html); a opção "Imagem pronta" dele foi descartada). A pré-visualização deve usar o mesmo CSS do monitor real e o tema do departamento, para não haver diferença entre o que se vê e o que sai.

## 10. Departamentos da UNEB

Fonte: página oficial [Multicampia e Departamentos](https://portal.uneb.br/multicampia-e-departamentos/), consultada em 2026-11-09. Os slugs usam sigla + número do campus. **[decidido para os repetidos; estendido aos demais por consistência, decisão do autor]**

| Nº | Campus | Departamentos (slug) |
|---|---|---|
| I | Salvador | DCH (`dch1`), DEDC (`dedc1`), DCET (`dcet1`), DCV (`dcv1`) |
| II | Alagoinhas | DEDC (`dedc2`), DCET (`dcet2`), DLLARTES (`dllartes2`) |
| III | Juazeiro | DTCS (`dtcs3`), DCH (`dch3`) |
| IV | Jacobina | DCH (`dch4`) |
| V | Santo Antônio de Jesus | DCH (`dch5`) |
| VI | Caetité | DCH (`dch6`) |
| VII | Senhor do Bonfim | DEDC (`dedc7`) |
| VIII | Paulo Afonso | DEDC (`dedc8`) |
| IX | Barreiras | DCH (`dch9`) |
| X | Teixeira de Freitas | DEDC (`dedc10`) |
| XI | Serrinha | DEDC (`dedc11`) |
| XII | Guanambi | DEDC (`dedc12`) |
| XIII | Itaberaba | DEDC (`dedc13`) |
| XIV | Conceição do Coité | DEDC (`dedc14`) |
| XV | Valença | DEDC (`dedc15`) |
| XVI | Irecê | DCHT (`dcht16`) |
| XVII | Bom Jesus da Lapa | DCHT (`dcht17`) |
| XVIII | Eunápolis | DCHT (`dcht18`) |
| XIX | Camaçari | DCHT (`dcht19`) |
| XX | Brumado | DCHT (`dcht20`) |
| XXI | Ipiaú | DCHT (`dcht21`) |
| XXII | Euclides da Cunha | DCHT (`dcht22`) |
| XXIII | Seabra | DCHT (`dcht23`) |
| XXIV | Xique-Xique | DCHT (`dcht24`) |
| — | Lauro de Freitas | DMCE (`dmce`) |
| — | Jeremoabo | DCT/OPARÁ (`opara`) |

Total: 26 campi com departamento e 32 departamentos. O Campus Avançado de Canudos não tem departamento na página oficial e fica de fora. A numeração dos campi vem da ordem oficial e dos nomes em uso (DCET II, DCHT XVI); deve ser conferida pelo Marcos.

O cadastro entra por um seeder opcional só da UNEB, para outra instituição não receber essa lista ao instalar. **[decisão do autor]**

## 11. Início em 2027.1 e dados de teste

O sistema entra em uso no semestre **2027.1**, começando limpo em produção. Não há dados a preservar. **[decidido]**

- Os dados atuais do DCET ficam como **dados de teste**, carregados por um seeder de demonstração em `dcet2`, e nunca rodam automaticamente em produção. **[decidido]**
- Na carga de teste, cada valor distinto de `horarios.curso` vira uma unidade do tipo curso. **[decisão do autor]**
- Não há conversão do banco antigo: as migrações podem criar a estrutura nova diretamente.
- O gestor cadastra as datas de 2027.1 no calendário. Instalação de outra instituição começa vazia pelo `/setup`. **[decisão do autor]**

## 12. Restrições técnicas

- Base existente: Laravel 12, Blade/Bootstrap no painel, HTML/JS puro em `public/` no monitor.
- O frontend do monitor usa URLs fixas (`/api/horarios`, `/api/config` etc.) e caminhos relativos (`assets/...`) em `index.html`. Com endereço por departamento, as chamadas precisam incluir o slug e os caminhos precisam ser absolutos, ou `/dcet2/` quebra.
- `/api/*` hoje é pública e continua pública, só de leitura, por departamento.
- O texto dos informes é digitado por usuários: deve ser exibido sem interpretar HTML. Uploads são validados pelo conteúdo do arquivo.
- As mudanças ainda não commitadas no repositório (correção da volta da internet, novos testes) devem ser preservadas.
- As vulnerabilidades do `composer audit` (25 avisos em 7 pacotes em 2026-11-04) devem ser resolvidas antes do deploy.

## 13. Critérios de aceite

- Dois departamentos com aulas diferentes: cada monitor mostra só as suas.
- Informe de departamento aparece só no próprio; informe da Reitoria aparece em todos.
- Nenhuma mensagem sai da tela antes do tempo calculado para sua leitura.
- Os informes vigentes podem ser lidos por inteiro e sem tempo no celular.
- Curso/unidade não consegue publicar imagem nem informe geral.
- Responsável de um departamento recebe bloqueio ao tentar acessar recurso de outro, testando diretamente as URLs.
- Laboratório não consegue cadastrar horário.
- Urgente interrompe o quadro de horários em até cerca de 60 segundos e pulsa de forma discreta.
- Fora de semestre, o monitor não mostra aulas e continua mostrando mensagens.
- A pré-visualização coincide com o monitor real.
- Com a carga de teste, os dados atuais do DCET aparecem em `/dcet2`.
- A suíte de testes atual continua passando, adaptada à nova estrutura, com testes novos de isolamento entre departamentos.

## 14. A cargo de quem construir

Nomes de tabelas e rotas internas, organização dos controllers, forma de aplicar o isolamento entre departamentos, armazenamento das imagens, fatiamento em etapas e layout das telas de cadastro, seguindo o padrão do painel atual.
