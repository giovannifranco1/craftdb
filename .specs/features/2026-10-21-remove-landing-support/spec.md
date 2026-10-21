# F7 — Remoção da landing + página Apoiar — Especificação

## Problema

O CraftDB Local ainda carrega a landing de marketing do SaaS (preços, depoimentos, termos), um formulário "Fale conosco" que grava numa tabela que ninguém lê, e não tem como o usuário apoiar o projeto open source.

## Objetivos

- [ ] Nenhuma página/arquivo de marketing do SaaS no projeto
- [ ] Página "Apoiar o projeto" acessível de dentro do app, sem nenhuma requisição externa
- [ ] Pendências pequenas do STATE resolvidas (aviso de CSS, Tailwind v3 morto, README defasado)

## Fora do Escopo

| Item | Motivo |
|---|---|
| Valores reais dos links de apoio | Ação do dev (A1) |
| Licença e nome do pacote no composer | Decisão do dev (A2, A3) |
| Tradução das ~60 chaves pt_BR pendentes | Passada de i18n futura (STATE) |

## User Stories

### P1: Sem landing ⭐ MVP

1. QUANDO as rotas são listadas ENTÃO NÃO DEVEM existir `/features`, `/pricing`, `/about`, `/terms`, `/policy`, `/login`, `/register` nem nomes `landing.*`, `login`, `register`; `/` DEVE redirecionar para `/dashboard` (LP-01)
2. QUANDO um request sem usuário chega a uma rota com `auth` (caso raro, ex.: middleware pulado) ENTÃO o redirect de guest DEVE ir para `/dashboard`, sem `RouteNotFoundException` (LP-02)
3. QUANDO o código é inspecionado ENTÃO NÃO DEVEM existir `LandingPageController`, views `pages/landing-page/**`, `components/landing-page/**`, layout `landing-page`, `resources/css/landing-page.css` (nem no Vite), `config/landingpage.php`, `lang/*/landing-page*.php`, `resources/markdown/{terms,policy}.md`, imagens `resources/images/landing-page/**`, `resources/images/auth/**`, `public/preview.png` (LP-03)
4. QUANDO `npm run build` roda ENTÃO NÃO DEVE gerar CSS da landing e NÃO DEVE haver aviso de sintaxe CSS (LP-04)

### P1: Sem "Fale conosco" ⭐ MVP

1. QUANDO qualquer tela do app é renderizada ENTÃO NÃO DEVE haver o formulário/modal "Fale conosco" nem o item de menu (LP-05)
2. QUANDO as migrations rodam ENTÃO a tabela `feedback` NÃO DEVE existir e `App\Models\Feedback` NÃO DEVE existir (LP-06)

### P1: Página Apoiar ⭐ MVP

1. QUANDO o usuário abre `/support` ENTÃO DEVE ver a página "Apoiar o projeto" no layout do app, com texto sobre o projeto open source (LP-07)
2. QUANDO `config('craftdb.support.repo_url')`, `github_sponsors_url`, `kofi_url`/`buymeacoffee_url` estão definidos ENTÃO a página DEVE mostrar o botão correspondente (estrela no repositório, GitHub Sponsors, Ko-fi/Buy Me a Coffee) abrindo em nova aba; QUANDO não estão, o botão NÃO DEVE aparecer (LP-08)
3. QUANDO `config('craftdb.support.pix_key')` está definido ENTÃO a página DEVE mostrar a chave Pix com botão de copiar; senão, a seção Pix NÃO DEVE aparecer (LP-09)
4. QUANDO nenhum link está configurado ENTÃO a página DEVE mostrar só o agradecimento e um aviso neutro ("formas de apoio em breve") (LP-10)
5. QUANDO a página é renderizada ENTÃO NÃO DEVE carregar recurso de host externo (só `<a href>` externos) (LP-11)
6. QUANDO o usuário abre o menu do usuário (app e canvas) ou clica no ♥ do rodapé ENTÃO DEVE haver acesso a "Apoiar o projeto" (LP-12)

### P2: Limpezas e docs

1. QUANDO o repositório é inspecionado ENTÃO `tailwind.config.js` e devDependencies só usadas por ele NÃO DEVEM existir, e `resources/css/filepond.css` NÃO DEVE ter texto fora de comentário (LP-13)
2. QUANDO o README (PT/EN) é lido ENTÃO NÃO DEVE haver seções de deploy SaaS, OAuth GitHub/Google, Equipes com papéis/convites nem Fortify/2FA (LP-14)
3. QUANDO `composer test`, `npm run build` e o smoke rodam ENTÃO DEVEM passar, e o smoke DEVE checar `/support` 200 sem host externo (LP-15)

## Rastreabilidade

| ID | Story | Fase | Status |
|---|---|---|---|
| LP-01 | Sem landing | Execute | Verified |
| LP-02 | Sem landing | Execute | Verified |
| LP-03 | Sem landing | Execute | Verified |
| LP-04 | Sem landing | Execute | Verified |
| LP-05 | Sem contato | Execute | Verified |
| LP-06 | Sem contato | Execute | Verified |
| LP-07 | Apoiar | Execute | Verified |
| LP-08 | Apoiar | Execute | Verified |
| LP-09 | Apoiar | Execute | Verified |
| LP-10 | Apoiar | Execute | Verified |
| LP-11 | Apoiar | Execute | Verified |
| LP-12 | Apoiar | Execute | Verified |
| LP-13 | Limpezas | Execute | Verified |
| LP-14 | Limpezas | Execute | Verified |
| LP-15 | Limpezas | Execute | Verified |

**Cobertura:** 15 total, 15 mapeados, 15 verificados (Pest + smoke LP-15)

## Critérios de Sucesso

- [ ] `grep -rniE "landing|LandingPage|landingpage|contact-us|Feedback|preview\.png|tailwind\.config" app bootstrap config routes resources database/factories tests vite.config.js package.json` só com hits legítimos (migrations antigas, testes de guarda, a palavra em comentários históricos) — listar
- [ ] `composer test`, `npm run build` (sem warning) e smoke verdes

**Status:** Aprovada pelo Lead (dev ausente)
**Data:** 2026-10-21
