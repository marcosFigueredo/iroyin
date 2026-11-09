# Testes E2 e E3

## E2 — falhas controladas

Os contratos HTTP e controles de segurança são executados pela suíte Laravel:

```bash
php artisan test
```

Os estados visuais e a conectividade são executados em Chromium pelo Playwright:

```bash
npm ci
npx playwright install chromium
npm run test:e2e
```

A suíte de navegador fixa a data em uma segunda-feira, simula as APIs sem
depender de serviços externos e cobre:

- zero aulas + zero notícias + zero editais + clima 204;
- ausência isolada de aulas, notícias e editais;
- rede offline, overlay visível, retorno online e retomada do ciclo sem erro JS.

A suíte Laravel cobre também editor bloqueado em configuração administrativa,
rejeição do papel inexistente `superAdmin`, formulário sem CSRF e bloqueio na
sexta tentativa de senha inválida.

## E3 — instalação LAMP convencional

Ambiente de referência validado: Ubuntu 24.04, Apache 2.4, MariaDB 10.11 e
PHP 8.3, sem Docker.

Em um Ubuntu limpo, instale os pré-requisitos:

```bash
sudo apt-get update
sudo DEBIAN_FRONTEND=noninteractive apt-get install -y \
  apache2 mariadb-server php-cli libapache2-mod-php php-mysql \
  php-mbstring php-xml php-curl php-zip php-bcmath composer unzip \
  nodejs npm curl
```

Depois, na raiz do repositório:

```bash
sudo bash tests/Installation/lamp-smoke.sh
```

O script cria uma cópia limpa sob `/var/www/iroyin-lamp-smoke.*`, instala
dependências do zero, cria somente o banco/usuário `iroyin_lamp_smoke`, executa
migrations e seed, configura um VirtualHost Apache com `mod_rewrite` e valida
as rotas públicas. A cópia é preservada ao final para auditoria.
