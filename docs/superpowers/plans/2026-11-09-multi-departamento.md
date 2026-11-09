# IROYIN multi-departamento: plano de implementação

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Transformar o IROYIN de um departamento único em um sistema com vários departamentos, perfis próprios, informes com urgência e monitor por endereço, rodando localmente.

**Architecture:** Um banco e um app Laravel. Cada linha de conteúdo local aponta para um departamento ou unidade; a API pública passa a ser `/api/{slug}/...`, e o monitor descobre o slug pelo endereço. A seleção do que vai à tela fica em um módulo JS puro, testado com `node --test`.

**Tech Stack:** Laravel 12, PHP 8.2+, Blade/Bootstrap 5, JS puro em `public/assets/js`, PHPUnit com SQLite em memória, Node (test runner nativo).

**Spec:** `docs/superpowers/specs/2026-11-09-multi-departamento-design.md`

## Global Constraints

- Todos os caminhos são relativos a `kiosk-admin/`.
- Título do informe: máximo 70 caracteres. Texto: máximo 160. Validar no servidor e no formulário.
- Imagem de informe: `image`, `mimes:jpg,jpeg,png,webp`, `max:4096`.
- Tempo de leitura: palavras ÷ 3 segundos, arredondado para cima, mínimo 10, máximo 30.
- Urgente: data de fim em no máximo 7 dias depois do início; até 3 por interrupção; consulta a cada 60 s.
- Pulsar urgente: contorno `.45cqw`, ciclo `2.6s`, fundo `#7a120d`. Selo importante `#e6b800`. Área da logo 24%.
- Perfis (`users.role`): `gestor`, `reitoria`, `departamento`, `unidade`.
- Tipos de unidade: `curso`, `laboratorio`, `centro`. Só `curso` tem horários.
- Temas válidos: as chaves atuais de `_TEMAS` (`azul`, `verde`, `vermelho`, `escuro`, `roxo`).
- Interface em pt-BR. Texto digitado por usuários nunca é interpretado como HTML.
- Compatível com SQLite (testes) e MySQL/MariaDB (servidor).
- Não fazer deploy no paciencia neste plano.
- Mensagens de commit terminam com `Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>`.

## Review Focus

1. Monitor aberto quando o departamento é desativado (API 404): mantém a tela com o fallback, sem quebrar. Teste na Task 8.
2. Último dia do semestre, às 23:30: o semestre ainda é vigente. Teste na Task 2.
3. Título com `<script>alert(1)</script>`: aparece como texto no monitor, no painel e no celular. Testes nas Tasks 7 e 9.
4. Departamento criado com slug de rota do sistema (`admin`, `api`) ou repetido: recusado com mensagem. Teste na Task 4.
5. Excluir curso que tem horários, informes ou usuários: bloqueado com mensagem, sem apagar em cascata. Teste na Task 4.

---

### Task 0: Preparação

- [ ] **Step 1:** Criar a branch `feat/multi-departamento`. O trabalho pendente no repositório entra antes, em um commit próprio (`chore: consolida trabalho pendente`), sem incluir `.env_local`.
- [ ] **Step 2:** Run: `php artisan test`. Expected: 69 passed.

### Task 1: Estrutura institucional

**Files:**
- Create: migrations de `campi`, `departamentos`, `unidades`; `app/Models/Campus.php`, `Departamento.php`, `Unidade.php`; `tests/Concerns/CriaEstrutura.php`
- Test: `tests/Feature/EstruturaTest.php`

**Interfaces:**
- Produces:
  - `campi`: `numero` (smallint, nulo), `nome`, `cidade`, `estado`. `Campus::cidadeClima(): string` → `"{cidade},BR"`.
  - `departamentos`: `campus_id`, `sigla`, `nome`, `slug` (único), `ativo` (true), `tema` ('azul'), `duracao_horarios` (120), `duracao_noticia` (30).
  - `Departamento::SLUGS_RESERVADOS`: `admin, api, login, logout, register, setup, dashboard, profile, build, assets, images, horarios, noticias, storage, up, acessivel, forgot-password, reset-password, verify-email, confirm-password, password, email`.
  - `Departamento::gerarSlug(string $sigla, ?int $numeroCampus): string` → minúsculas, sem acento, sigla + número.
  - `Departamento::resolveRouteBinding`: com campo `slug`, só encontra ativos.
  - `unidades`: `departamento_id`, `tipo` (string), `nome`, `ativo`. `Unidade::temHorarios(): bool`.
  - `CriaEstrutura`: `criaDepartamento(string $slug = 'dcet2'): Departamento`, `criaCurso(Departamento $d, string $nome = 'Sistemas de Informação'): Unidade`, `criaUsuario(string $role, ?Departamento $d = null, ?Unidade $u = null): User`, `criaSemestreVigente(): Semestre`.

- [ ] **Step 1:** Escrever `test_gera_slug_com_numero_do_campus` (`('DCET', 2)` → `dcet2`; `('DMCE', null)` → `dmce`), `test_laboratorio_nao_tem_horarios`, `test_curso_tem_horarios`, `test_slug_de_departamento_inativo_nao_resolve`.
- [ ] **Step 2:** Run: `php artisan test --filter=EstruturaTest`. Expected: FAIL.
- [ ] **Step 3:** Implementar migrations, modelos e relações (`Campus hasMany Departamento`, `Departamento hasMany Unidade`).
- [ ] **Step 4:** Run: `php artisan test --filter=EstruturaTest`. Expected: PASS.
- [ ] **Step 5:** Commit: `feat: adiciona campus, departamento e unidade`.

### Task 2: Calendário acadêmico

**Files:**
- Create: migration em `semestres`
- Modify: `app/Models/Semestre.php`, `app/Http/Controllers/Admin/SemestreController.php`, `resources/views/admin/semestres/*`, `routes/web.php`, `tests/Concerns/CriaEstrutura.php`
- Test: `tests/Feature/Admin/CalendarioTest.php`

**Interfaces:**
- Produces: colunas `inicio`, `fim` (date); coluna `ativo` removida; rota `semestres.ativar` removida; `Semestre::vigente(): ?Semestre`, que compara só a data e inclui os dois extremos; `Semestre::sobrepoe(Carbon $inicio, Carbon $fim, ?int $ignorarId = null): bool`.

- [ ] **Step 1:** Escrever `test_semestre_vigente_pela_data`, `test_ultimo_dia_as_23h30_ainda_e_vigente`, `test_fora_de_semestre_nao_ha_vigente`, `test_semestres_sobrepostos_sao_recusados`, `test_fim_antes_do_inicio_e_recusado`, `test_semestre_vigente_nao_pode_ser_excluido`.
- [ ] **Step 2:** Run: `php artisan test --filter=CalendarioTest`. Expected: FAIL.
- [ ] **Step 3:** Implementar. Trocar os usos de `where('ativo', true)` em `AcessivelController` e `Api\HorarioController` por `Semestre::vigente()` e ajustar os testes que criam semestre com `ativo`.
- [ ] **Step 4:** Run: `php artisan test`. Expected: PASS.
- [ ] **Step 5:** Commit: `feat: calendario academico com semestre vigente por data`.

### Task 3: Perfis e escopo

**Files:**
- Create: migration em `users`; `app/Http/Middleware/PerfilMiddleware.php`
- Modify: `app/Models/User.php`, `bootstrap/app.php`, `app/Http/Controllers/SetupController.php`, `routes/web.php`, `resources/views/layouts/*`, `tests/Feature/AccessControlTest.php`, `tests/Feature/SecurityControlsTest.php`, `tests/Feature/SetupTest.php`
- Test: `tests/Feature/PerfisTest.php`

**Interfaces:**
- Produces:
  - `users.role` vira string; `users.departamento_id` e `users.unidade_id` nulos.
  - `User::isGestor()`, `isReitoria()`, `isDepartamento()`, `isUnidade()`. `isAdmin()` passa a ser `isGestor()`.
  - Middleware `perfil:gestor,departamento` (403 fora da lista). O alias `admin` continua e significa gestor.
  - `User::escopoDepartamentoId(): ?int`: departamento do usuário; para gestor, `session('operar_departamento_id')`.
  - Rota `POST admin/operar-como` (`admin.operar-como`), só gestor; `departamento_id` vazio encerra o modo.
  - O setup cria usuário `gestor`.

- [ ] **Step 1:** Escrever `test_setup_cria_gestor`, `test_perfil_fora_da_lista_recebe_403`, `test_gestor_opera_como_departamento`, `test_departamento_nao_usa_operar_como`.
- [ ] **Step 2:** Run: `php artisan test --filter=PerfisTest`. Expected: FAIL.
- [ ] **Step 3:** Implementar. Colocar feeds, agências, notícias, editais, semestres, instituição e chave de clima no grupo `perfil:gestor`. O menu mostra só o que o perfil pode usar.
- [ ] **Step 4:** Run: `php artisan test`. Expected: PASS, com os testes de acesso adaptados.
- [ ] **Step 5:** Commit: `feat: perfis gestor, reitoria, departamento e unidade`.

### Task 4: Cadastros no painel

**Files:**
- Create: `app/Http/Controllers/Admin/CampusController.php`, `DepartamentoController.php`, `UnidadeController.php`, `DepartamentoConfigController.php`; views em `resources/views/admin/{campi,departamentos,unidades,departamento-config}`
- Modify: `app/Http/Controllers/Admin/UserController.php`, `InstituicaoController.php`, `ConfiguracaoController.php`, as views de usuários, instituição e configurações, `routes/web.php`; migration removendo `instituicao.departamento` e `configuracoes.{cidade_clima, duracao_horarios, duracao_noticia, tema}`
- Test: `tests/Feature/Admin/CadastrosTest.php`

**Interfaces:**
- Consumes: Tasks 1 e 3.
- Produces: rotas `admin.campi.*` e `admin.departamentos.*` (gestor); `admin.unidades.*` e `admin.departamento-config.{edit,update}` (departamento e gestor em modo operar); `Unidade` buscada sempre dentro de `escopoDepartamentoId()`.

- [ ] **Step 1:** Escrever:
  - `test_gestor_cria_departamento_e_slug_e_gerado`
  - `test_slug_reservado_e_recusado`, `test_slug_repetido_e_recusado`
  - `test_departamento_cria_curso_e_secretario`
  - `test_departamento_nao_acessa_unidade_de_outro` (404 em GET/PUT/DELETE direto na URL)
  - `test_excluir_curso_com_horarios_e_bloqueado`, `test_excluir_curso_com_informes_e_bloqueado`, `test_excluir_curso_com_usuarios_e_bloqueado`
  - `test_departamento_nao_cria_usuario_gestor`
  - `test_departamento_altera_tema_e_tempos_proprios`
- [ ] **Step 2:** Run: `php artisan test --filter=CadastrosTest`. Expected: FAIL.
- [ ] **Step 3:** Implementar seguindo o padrão Bootstrap dos cadastros atuais. A Task 6 cria `informes`; até lá, proteger a verificação com `Schema::hasTable` e removê-la na Task 6.
- [ ] **Step 4:** Run: `php artisan test`. Expected: PASS.
- [ ] **Step 5:** Commit: `feat: cadastros de campi, departamentos, unidades e usuarios`.

### Task 5: Horários por curso e API por departamento

**Files:**
- Create: migration em `horarios`
- Modify: `app/Models/Horario.php`, `app/Http/Controllers/Admin/HorarioController.php`, `resources/views/admin/horarios/*`, `routes/api.php`, os 5 controllers em `app/Http/Controllers/Api`, `tests/Feature/Api/*`, `tests/Feature/Admin/HorarioTest.php`, `tests/Feature/KioskFailureModesTest.php`
- Test: `tests/Feature/Api/MonitorDepartamentoTest.php`

**Interfaces:**
- Produces:
  - `horarios.unidade_id` obrigatório, sem cascata; coluna `curso` removida.
  - Rotas `GET /api/{departamento:slug}/{config,clima,horarios,noticias,editais}`. As rotas antigas sem slug deixam de existir.
  - `config`: campos atuais, mais `slug`, `logo_url`, `cabecalho` = `"{SIGLA} · {NOME} · {CIDADE}"` em maiúsculas, com a sigla + número (`DCET2`); `url_acessivel` = `url("/{slug}/acessivel")`.
  - `horarios`: mantém o campo `curso` na resposta, vindo de `unidade.nome`.
  - `clima`: usa `campus->cidadeClima()` e a chave de `Configuracao`.
  - Importação CSV: a coluna `curso` casa com unidade do departamento pelo nome; linha sem correspondência é rejeitada e contada na mensagem. Para o perfil `unidade`, a unidade é sempre a do usuário.

- [ ] **Step 1:** Escrever `test_monitor_mostra_so_aulas_do_proprio_departamento`, `test_slug_inexistente_retorna_404`, `test_departamento_inativo_retorna_404`, `test_config_traz_cabecalho_e_url_acessivel_do_departamento`, `test_fora_de_semestre_nao_retorna_aulas`, `test_laboratorio_nao_cadastra_horario`, `test_secretaria_nao_altera_horario_de_outro_curso`, `test_csv_com_curso_desconhecido_e_rejeitado`.
- [ ] **Step 2:** Run: `php artisan test --filter=MonitorDepartamentoTest`. Expected: FAIL.
- [ ] **Step 3:** Implementar.
- [ ] **Step 4:** Run: `php artisan test`. Expected: PASS.
- [ ] **Step 5:** Commit: `feat: horarios por curso e API por departamento`.

### Task 6: Informes (servidor)

**Files:**
- Create: migration `informes`; `app/Models/Informe.php`; `app/Policies/InformePolicy.php`; `app/Http/Controllers/Admin/InformeController.php`; `app/Http/Controllers/Api/InformeController.php`; `app/Support/ImagemUpload.php`
- Modify: `app/Http/Controllers/Admin/NoticiaController.php` (usar `ImagemUpload`), `routes/web.php`, `routes/api.php`, `app/Http/Controllers/Admin/UnidadeController.php` (remover o `Schema::hasTable`)
- Test: `tests/Feature/Admin/InformeTest.php`, `tests/Feature/Api/InformesApiTest.php`

**Interfaces:**
- Produces:
  - `informes`: `departamento_id` (nulo = Reitoria), `unidade_id` (nulo), `user_id`, `formato` (`texto`|`imagem`), `titulo` (70), `texto` (160, nulo em imagem), `imagem` (nulo), `urgencia` (`normal`|`importante`|`urgente`), `inicio`, `fim` (23:59:59).
  - `Informe::vigentes()`; `Informe::paraMonitor(Departamento $d)`: vigentes do departamento ou da Reitoria; `Informe::origem(): string` → `"{unidade} · DCET2"`, `"DCET2"` ou `"Reitoria"`.
  - Rotas `admin.informes.*` (reitoria, departamento, unidade), escopadas por policy.
  - `GET /api/{departamento:slug}/informes` → `{"informes":[{id, formato, titulo, texto, imagem, urgencia, origem, escopo: "departamento"|"reitoria"}]}`.

- [ ] **Step 1:** Escrever:
  - `InformesApiTest`: `test_informe_do_departamento_nao_aparece_em_outro`, `test_informe_da_reitoria_aparece_em_todos`, `test_informe_vencido_nao_aparece`, `test_informe_futuro_nao_aparece`.
  - `InformeTest`: `test_titulo_acima_de_70_e_recusado`, `test_texto_acima_de_160_e_recusado`, `test_unidade_nao_publica_imagem`, `test_departamento_publica_imagem`, `test_reitoria_publica_informe_geral`, `test_unidade_nao_publica_para_outra_unidade`, `test_departamento_nao_altera_informe_de_outro`, `test_urgente_com_mais_de_7_dias_e_recusado`, `test_secretaria_marca_urgente_no_proprio_departamento`, `test_svg_e_recusado`.
- [ ] **Step 2:** Run: `php artisan test --filter=Informe`. Expected: FAIL.
- [ ] **Step 3:** Implementar. O `departamento_id` e o `unidade_id` vêm sempre do usuário autenticado, nunca do formulário.
- [ ] **Step 4:** Run: `php artisan test`. Expected: PASS.
- [ ] **Step 5:** Commit: `feat: informes com urgencia e origem`.

### Task 7: Painel do informe com pré-visualização

**Files:**
- Create: `public/assets/css/informe.css`, `public/assets/js/temas.js`, `public/assets/js/informeRender.js`; `resources/views/admin/informes/{index,create,edit,_form}.blade.php`
- Modify: `public/assets/js/config.js` (usar `temas.js`)
- Test: `tests/Feature/Admin/InformeTelaTest.php`

**Interfaces:**
- Produces:
  - `temas.js`: `window.KIOSK_TEMAS` e `window.aplicarTemaInforme(el, nomeTema)`, que define variáveis CSS em `el`.
  - `informeRender.js`: `window.renderInforme(container, item, logoUrl)`. Monta o quadro só com `textContent`; aceita o formato JSON da Task 6.
  - Classes `.informe-tela`, `.informe-tela.urgente`, `.informe-tela.importante`, com tamanhos em `cqw`, no padrão do protótipo em `docs/superpowers/specs/2026-11-09-multi-departamento-assets/informe-painel-v3.html`.

- [ ] **Step 1:** Escrever `test_formulario_carrega_o_mesmo_css_e_render_do_monitor`, `test_formulario_tem_limites_70_e_160`, `test_unidade_nao_ve_opcao_de_imagem`, `test_listagem_escapa_titulo_com_script`.
- [ ] **Step 2:** Run: `php artisan test --filter=InformeTelaTest`. Expected: FAIL.
- [ ] **Step 3:** Implementar. O formulário fica à esquerda e o monitor em miniatura à direita, atualizando a cada tecla, com contador de caracteres e o tema do departamento. Sem seletor de alcance.
- [ ] **Step 4:** Run: `php artisan test`. Expected: PASS.
- [ ] **Step 5:** Commit: `feat: painel de informes com pre-visualizacao`.

### Task 8: Monitor por departamento e critérios de exibição

**Files:**
- Rename: `public/index.html` → `public/monitor.html`. Com `index.html` na pasta, o Apache responderia `/` sem passar pelo Laravel.
- Create: `public/assets/js/criterios.js`, `tests/js/criterios.test.cjs`, `resources/views/departamentos.blade.php`
- Modify: `routes/web.php`, `public/assets/js/{config,kiosk,classTime,weather,editais}.js`, `tests/Browser/*`, `package.json` (script `test:js`)
- Test: `tests/Feature/MonitorRotaTest.php`, `tests/js/criterios.test.cjs`

**Interfaces:**
- Consumes: Tasks 5, 6 e 7.
- Produces, em `criterios.js` (global no navegador, `module.exports` no Node):
  - `tempoLeitura(texto: string, tetoS = 30): number`. Regra das Global Constraints; o teto nunca reduz abaixo de 10.
  - `prioridade(item): number`. urgente 0; importante 1; informe de departamento 2; informe da Reitoria 3; notícia e edital 4.
  - `montarBloco(itens, ultimaVez: {[chave]: ms}, limiteS: number, tetoNoticiaS: number): item[]`. Ordena por `prioridade`, depois por quem apareceu há mais tempo. Acumula enquanto couber em `limiteS`, sempre inclui ao menos um e nunca reduz o tempo de um item.
  - `selecionarUrgentes(itens): item[]`. Departamento antes de Reitoria, mais recente primeiro, no máximo 3.
  - Chave de item: `"{tipo}:{id}"`.
  - `limiteBloco(cfg): number` = `max(cfg.duracao_horarios, 4 * cfg.duracao_noticia)`.
- Rotas: `/` lista os departamentos ativos; `/{departamento:slug}` serve `monitor.html`, com `where('[a-z0-9-]+')`, como última rota do arquivo.

- [ ] **Step 1:** Escrever em `criterios.test.cjs`:
  - `tempoLeitura`: 3 palavras → 10; 45 palavras → 15; 200 palavras → 30; teto 5 com 3 palavras → 10.
  - `montarBloco`: urgente vem antes de importante; informe do departamento vem antes da Reitoria; dentro do grupo, o mais antigo vem primeiro; item que não cabe fica fora e inteiro; lista com um item maior que o limite retorna esse item.
  - `selecionarUrgentes`: 5 de entrada → 3 de saída, departamento primeiro.
- [ ] **Step 2:** Escrever em `MonitorRotaTest`: `test_raiz_lista_departamentos_ativos`, `test_slug_serve_o_monitor`, `test_slug_inativo_retorna_404`, `test_rotas_do_sistema_nao_viram_monitor` (`/login` continua sendo login), `test_monitor_usa_caminhos_absolutos` (conteúdo sem `href="assets/`, `src="assets/`).
- [ ] **Step 3:** Run: `npm run test:js` e `php artisan test --filter=MonitorRotaTest`. Expected: FAIL.
- [ ] **Step 4:** Implementar a rota, a lista e os caminhos absolutos em `monitor.html`, incluindo `#informe-section`. Em `config.js`: `window.KIOSK_SLUG` (1º segmento do caminho) e `window.kioskApi(recurso)` → `/api/{slug}/{recurso}?_=...`; `if (!r.ok) throw` antes de `r.json()`, mantendo o fallback; cabeçalho vem de `cfg.cabecalho`.
- [ ] **Step 5:** Reescrever o ciclo em `kiosk.js`, sem `_getHalf`: horários → banner → `montarBloco` com informes, notícias e editais → horários. Cada item usa `tempoLeitura`, com `cfg.duracao_noticia` como teto em notícias e editais. `ultimaVez` persiste em `localStorage` com `try/catch`. O modo Boa Noite usa `montarBloco` sem limite. Preservar `pauseKioskCycle` e `resumeKioskCycle`.
- [ ] **Step 6:** Implementar a consulta de urgentes a cada 60 s: urgente novo (por id) interrompe o quadro de horários, mostra `selecionarUrgentes` e retoma o ciclo. Nas voltas seguintes ele entra por `montarBloco`, sem interromper de novo.
- [ ] **Step 7:** Atualizar `tests/Browser` para usar `/dcet2`, com casos: `urgente interrompe horarios e pulsa`; `departamento desativado mantem a tela com fallback`; `sem mensagens os horarios continuam`.
- [ ] **Step 8:** Run: `npm run test:js`, `php artisan test`, `npx playwright test`. Expected: PASS.
- [ ] **Step 9:** Commit: `feat: monitor por departamento e criterios de exibicao`.

### Task 9: Versão do celular

**Files:**
- Modify: `app/Http/Controllers/AcessivelController.php`, `resources/views/acessivel.blade.php`, `routes/web.php`, `public/acessivel-manifest.json` (vira rota dinâmica), `public/acessivel-sw.js` se depender do caminho antigo
- Test: `tests/Feature/AcessivelTest.php`

**Interfaces:**
- Consumes: `Informe::paraMonitor`, `Semestre::vigente`.
- Produces: `GET /{departamento:slug}/acessivel`; `GET /{departamento:slug}/acessivel/manifest.json` com `start_url` do departamento; `GET /acessivel` redireciona para `/`.

- [ ] **Step 1:** Escrever `test_mostra_aulas_so_do_departamento`, `test_lista_informes_na_integra_urgentes_primeiro`, `test_inclui_informes_da_reitoria`, `test_nao_inclui_informe_de_outro_departamento`, `test_titulo_com_script_e_escapado`, `test_manifest_aponta_para_o_departamento`.
- [ ] **Step 2:** Run: `php artisan test --filter=AcessivelTest`. Expected: FAIL.
- [ ] **Step 3:** Implementar. A rota vem antes de `/{departamento:slug}`.
- [ ] **Step 4:** Run: `php artisan test`. Expected: PASS.
- [ ] **Step 5:** Commit: `feat: informes na integra na versao do celular`.

### Task 10: Carga da UNEB e dados de teste

**Files:**
- Create: `database/seeders/UnebSeeder.php`, `database/seeders/TesteDcetSeeder.php`, `database/seeders/data/` (no `.gitignore`, contém nomes reais)
- Modify: `database/seeders/DemoSeeder.php`, `.gitignore`
- Test: `tests/Feature/SeedersTest.php`

**Interfaces:**
- Produces: `UnebSeeder` com os valores da tabela da seção 10 do spec, idempotente por `slug`. `TesteDcetSeeder` cria 1 usuário de cada perfil, os cursos e os horários de `dcet2`, um semestre vigente e informes de exemplo nos três níveis. Lê `database/seeders/data/dcet2_horarios.csv`, extraído de `../iroyin_dados.sql`; sem o arquivo, usa os dados do `DemoSeeder`.

- [ ] **Step 1:** Escrever `test_uneb_tem_26_campi_e_32_departamentos`, `test_uneb_contem_dcet1_e_dcet2`, `test_uneb_pode_rodar_duas_vezes`, `test_dados_de_teste_nao_rodam_em_producao`, `test_instalacao_nova_comeca_sem_departamentos`.
- [ ] **Step 2:** Run: `php artisan test --filter=SeedersTest`. Expected: FAIL.
- [ ] **Step 3:** Implementar e gerar o CSV. Em produção, `TesteDcetSeeder` lança exceção.
- [ ] **Step 4:** Run: `php artisan test`. Expected: PASS.
- [ ] **Step 5:** Commit: `feat: carga da UNEB e dados de teste`.

### Task 11: Dependências e execução local

**Files:**
- Modify: `composer.lock`, `README.md`, `CHANGELOG.md`

- [ ] **Step 1:** Run: `composer update`, depois `composer audit`. Expected: nenhum aviso. Se sobrar algum sem correção disponível, registrar.
- [ ] **Step 2:** Run: `php artisan test`, `npm run test:js`, `npm run build`. Expected: PASS.
- [ ] **Step 3:** Criar banco SQLite local em `database/iroyin-local.sqlite`, sem alterar o `.env` nem o MySQL: `DB_CONNECTION=sqlite DB_DATABASE=<caminho> php artisan migrate:fresh`, depois `db:seed --class=UnebSeeder` e `db:seed --class=TesteDcetSeeder`.
- [ ] **Step 4:** Subir com `php artisan serve` e conferir no navegador: `/`, `/dcet2`, `/dcet1`, `/dcet2/acessivel`, login de cada perfil, publicação de um urgente vista no monitor em até 60 s.
- [ ] **Step 5:** Documentar perfis, endereços e cargas no `README.md` e no `CHANGELOG.md`.
- [ ] **Step 6:** Commit: `chore: atualiza dependencias e documenta multi-departamento`.
