# Contexto — F5 Zero telemetria

**Origem:** ROADMAP M1/F5 · PROJECT.md objetivo "zero requisições de rede para fora da máquina" · regra: 1 commit por fase, sem push
**Data:** 2026-10-21
**Escopo:** Medium/Large · defaults (subagents + two-stage review)
**Aprovações:** dev ausente — decisões do **Lead** seguindo as opções recomendadas

## Decisões (Lead, dev ausente)

| # | Decisão | Alternativas descartadas |
|---|---|---|
| D1 | **Remover `laravel/nightwatch` e `resend/resend-laravel`** (pacotes, provider explícito em `bootstrap/providers.php`, config, env) | Manter desligados por env |
| D2 | **Remover configs de nuvem sem uso**: disco `s3`, `services` (postmark, ses, resend, slack), mailers `postmark`/`resend`/`ses`/`failover`, canais de log `slack`/`papertrail`, conexão de fila `sqs`, store de cache `dynamodb`, variáveis `AWS_*`/`NIGHTWATCH_*`/`RESEND_*`/`POSTMARK_*` no `.env.example`, `NIGHTWATCH_ENABLED` no compose | Manter configs inertes |
| D3 | **Fontes self-hosted via `@fontsource`** (Poppins 300/400/500/600/700 + itálico 400; Outfit; Kalam 300/400/700; Space Mono 400/700), importadas no CSS e empacotadas pelo Vite; nenhuma `<link>` para Google Fonts em lugar nenhum (app, canvas, erros, `mcp.authorize`, landing) | Baixar woff2 à mão para `resources/fonts` |
| D4 | **Landing já sem chamadas externas na F5**: remover script GoAdopt e trocar `og:url`/`og:image` absolutos (`craftdb.app`) por URLs relativas ao `APP_URL` (a landing inteira sai na F7) | Esperar a F7 (GoAdopt fica ativo no Docker até lá) |
| D5 | **Teste de regressão** que renderiza páginas do app (dashboard, settings, diagrama, erros 404/500, `mcp.authorize` se renderizável, uma página da landing) e falha se o HTML referenciar host externo (qualquer `http(s)://` ou `//` que não seja o `APP_URL`, ignorando namespaces SVG e links `<a href>` de documentação) | Só grep manual |

## Decisões do agente

| # | Decisão | Motivo |
|---|---|---|
| A1 | Apagar `resources/views/components/layouts/app/header.blade.php` (starter-kit, sem uso; links para GitHub do starter-kit — item do STATE) | Código morto com links externos |
| A2 | Páginas de erro passam a usar `@vite` com o CSS do app (em vez de `<link>` externo) | Fonte local também nos erros |
| A3 | `tailwind.config.js` (estilo v3, provavelmente não carregado pelo Tailwind 4) fica — fora de escopo; anotar no STATE | Não é telemetria |
| A4 | Smoke test ganha check: HTML de `/dashboard` sem `fonts.googleapis`/`gstatic`/`goadopt` | Prova no Docker |
| A6 | **Não** usar `dontMergeFrameworkConfiguration()`: defaults de nuvem que o Laravel 13 mescla por baixo ficam (inertes, nenhum selecionado); ZT-06 verifica os arquivos `config/*.php` do app | Desligar o merge exigiria republicar configs e manter defaults à mão a cada upgrade (Lead, 2026-10-21) |
| A5 | `mcp.authorize`: só troca a fonte; favicon/manifest 404 locais ficam para a F6 | Escopo |

## Fatos (mapeamento 2026-10-21)

- Servidor no Docker já não faz chamadas externas (Nightwatch `NIGHTWATCH_ENABLED=false`, `MAIL_MAILER=log`, driver S3 não instalado, nenhum `Http::` no app)
- Vazamentos ativos: Google Fonts em `_partials/head.blade.php`, `_partials/head-canvas.blade.php`, `errors/{404,500,503}.blade.php`, `mcp/authorize.blade.php`, `layouts/landing-page.blade.php`; GoAdopt em `layouts/landing-page.blade.php:39-43` (ativo com `APP_ENV=production`)
- Fontes usadas: Poppins (`--font-sans`), Outfit (logo, erros, landing), Kalam (canvas), Space Mono (nomes de arquivo no import)
- Pesos usados nas views: 300–700 (font-light…font-bold)
- `phpunit.xml` não tem `NIGHTWATCH_ENABLED=false` (some com a remoção do pacote)
