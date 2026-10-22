# Contexto — F7 Remoção da landing + página Apoiar

**Origem:** ROADMAP M1/F7 (última fase do M1) · PROJECT.md (página "Apoiar": GitHub Sponsors, Buy Me a Coffee/Ko-fi, Pix, estrela) · regra: 1 commit por fase, sem push
**Data:** 2026-10-21
**Escopo:** Large · defaults (subagents + two-stage review)
**Aprovações:** dev ausente — decisões do **Lead** seguindo as opções recomendadas

## Decisões (Lead, dev ausente)

| # | Decisão | Alternativas descartadas |
|---|---|---|
| D1 | **Remover a landing inteira**: rotas `/features`, `/pricing`, `/about`, `/terms`, `/policy`; `LandingPageController`; `resources/views/pages/landing-page/**`; `components/landing-page/**`; layout `landing-page`; `resources/css/landing-page.css` (+ input do Vite); `config/landingpage.php`; `lang/*/landing-page*.php`; `resources/markdown/{terms,policy}.md`; imagens `resources/images/landing-page/**`, `public/preview.png`, `resources/images/auth/**` (sem uso) | Manter `/terms` e `/policy` |
| D2 | **Rotas de entrada**: `/` continua redirect para `/dashboard` (sem o nome `landing.index`); `/login` e `/register` saem; fallback de guest via `$middleware->redirectGuestsTo('/dashboard')` (Laravel usa `route('login')` por padrão) | Manter `login`/`register` como redirects |
| D3 | **Remover o "Fale conosco"** (`ui/⚡contact-us`, `App\Models\Feedback`, menu nos 2 user menus); migration nova apaga a tabela `feedback` | Manter o form gravando numa tabela que ninguém lê |
| D4 | **Página "Apoiar o projeto"** em `/support` (rota `support`), no layout do app, com: estrela no repositório, GitHub Sponsors, Ko-fi/Buy Me a Coffee e chave Pix copiável; links vêm de `config('craftdb.support.*')` (env `CRAFTDB_SUPPORT_*`); **link não configurado não aparece**; sem nenhum configurado, a página mostra só o texto de agradecimento e "em breve". Sem imagens/QR externos (zero rede) | QR code Pix gerado (sem lib); links fixos no código |
| D5 | **Entradas para o Apoiar**: item "Apoiar o projeto" (ícone coração/presente) nos 2 user menus (`master`, `topbar`) no lugar do "Fale conosco", e o ♥ do rodapé do app linka para `/support` | Item no menu lateral (o `heart` já é Favoritos) |
| D6 | **Limpezas pendentes do STATE** que cabem aqui: comentário quebrado em `resources/css/filepond.css:17` (aviso do esbuild); `tailwind.config.js` morto + devDependencies órfãs (`@tailwindcss/forms`, `tailwindcss-animate`, `daisyui`) se sem uso; README (PT/EN) sem "Deploy em Produção" SaaS e sem §6 Equipes / §7 Autenticação defasados | — |

## Decisões do agente

| # | Decisão | Motivo |
|---|---|---|
| A1 | Links de apoio ficam `null` por padrão; registrar no STATE como **ação do dev** preencher (repo, Sponsors, Ko-fi/BMC, Pix) | Não existem no repo; dev ausente |
| A2 | Licença (MIT no README vs AGPL sugerida no PROJECT.md) **não** é decidida aqui — registrar no STATE | Decisão do dev |
| A3 | `composer.json` `name` (`laravel/livewire-starter-kit`) fica — registrar no STATE | Decisão do dev (nome do pacote) |
| A4 | Teste ZT03 (landing sem host externo) é removido; a página `/support` entra no dataset ZT01 | Landing não existe mais |

## Fatos (mapeamento 2026-10-21)

- Nada no app usa `route('landing.*')`; erros 404/500/503 linkam `url('/')` (redirect fica)
- `config/landingpage.php` não é lido por nada; `resources/markdown/*.md` não são lidos; `statistics`/`testimonials` nem incluídos
- `contact-us` é renderizado em `layouts/app/master.blade.php:94` e `layouts/canvas/index.blade.php:43`, com gatilhos nos user menus
- Preline é usado pelo app — fica
- Remote git é GitLab (`gitlab.com:giovannirz/craftdb`)
