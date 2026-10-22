# F5 — Zero telemetria — Especificação

## Problema

O CraftDB Local promete que nada sai da máquina, mas o navegador ainda busca fontes no Google em toda página, a landing carrega um script de terceiros (GoAdopt) e aponta `og:` para `craftdb.app`, e o projeto carrega pacotes e configurações de serviços externos (Nightwatch, Resend, S3, SES, Postmark, Slack, SQS, DynamoDB).

## Objetivos

- [ ] Uso normal do app não faz nenhuma requisição para fora da máquina (servidor e navegador)
- [ ] Nenhum pacote ou configuração de telemetria/serviço externo no projeto
- [ ] Teste automatizado que impede regressão

## Fora do Escopo

| Item | Motivo |
|---|---|
| Remover a landing | F7 |
| Passport/MCP, favicon/manifest do `mcp.authorize` | F6 |
| Links `<a href>` externos clicáveis (não são requisições) | Não geram tráfego automático |
| Rede no build da imagem (composer/npm/base images) | Build ≠ uso normal |

## User Stories

### P1: Navegador sem requisições externas ⭐ MVP

1. QUANDO qualquer página do app (dashboard, settings, diagrama, erros 404/500/503, `mcp.authorize`) é renderizada ENTÃO o HTML NÃO DEVE referenciar recursos em host externo (`<link>`, `<script>`, `<img>`, `@import`, `url()`), só caminhos locais/`APP_URL` (ZT-01)
2. QUANDO as fontes Poppins, Outfit, Kalam e Space Mono são usadas ENTÃO DEVEM vir de arquivos locais empacotados pelo Vite (`@fontsource`), mantendo a aparência atual (ZT-02)
3. QUANDO uma página da landing é renderizada ENTÃO NÃO DEVE haver script GoAdopt, Google Fonts nem `og:url`/`og:image` apontando para `craftdb.app` (ZT-03)
4. QUANDO o CSS compilado (`public/build`) é inspecionado ENTÃO NÃO DEVE conter `@import`/`url()` para host externo (ZT-04)

### P1: Servidor sem serviços externos ⭐ MVP

1. QUANDO o `composer.json` é inspecionado ENTÃO `laravel/nightwatch` e `resend/resend-laravel` NÃO DEVEM estar presentes e o app DEVE subir sem eles (ZT-05)
2. QUANDO os arquivos de configuração do app (`config/*.php`) são inspecionados ENTÃO NÃO DEVEM definir: disco `s3`; `services.postmark|ses|resend|slack`; mailers `postmark|resend|ses|failover`; canais de log `slack|papertrail`; fila `sqs`; cache `dynamodb`; e a rota `resend.webhook` NÃO DEVE existir. Defaults inertes que o framework mescla por baixo (não selecionados por nenhuma env) são aceitos — não geram tráfego (ZT-06)
3. QUANDO `.env.example` e `docker-compose.yml` são inspecionados ENTÃO NÃO DEVEM existir variáveis `AWS_*`, `NIGHTWATCH_*`, `RESEND_*`, `POSTMARK_*`, `SLACK_*`/`LOG_SLACK_*`, `PAPERTRAIL_*` (ZT-07)
4. QUANDO o app roda ENTÃO e-mail (só o formulário de contato da landing, se houver) usa o mailer `log` (ZT-08)

### P2: Prova e docs

1. QUANDO o smoke Docker roda ENTÃO o HTML de `/dashboard` e de um diagrama NÃO DEVE conter `fonts.googleapis`, `fonts.gstatic`, `goadopt` ou `craftdb.app` (ZT-09)
2. QUANDO `composer test`, `npm run build` e o smoke rodam ENTÃO DEVEM passar (ZT-10)

## Casos Limite

- Namespaces SVG (`http://www.w3.org/2000/svg`) não são requisições → ignorados pelo teste
- `<a href="https://...">` de documentação/créditos → permitidos (não carregam nada)
- Layout `app/header` (starter-kit) sem uso → removido (A1)

## Rastreabilidade

| ID | Story | Fase | Status |
|---|---|---|---|
| ZT-01 | Navegador | Execute | Verified |
| ZT-02 | Navegador | Execute | Verified |
| ZT-03 | Navegador | Execute | Verified |
| ZT-04 | Navegador | Execute | Verified |
| ZT-05 | Servidor | Execute | Verified |
| ZT-06 | Servidor | Execute | Verified |
| ZT-07 | Servidor | Execute | Verified |
| ZT-08 | Servidor | Execute | Verified |
| ZT-09 | Prova | Execute | Verified |
| ZT-10 | Prova | Execute | Verified |

**Cobertura:** 10 total, 10 mapeados, 10 verificados (Pest + smoke ZT-09)

## Critérios de Sucesso

- [ ] `grep -rniE "fonts\.googleapis|fonts\.gstatic|goadopt|craftdb\.app|nightwatch|resend|postmark|papertrail|dynamodb|sqs|AWS_" app bootstrap config routes resources docker docker-compose.yml .env.example composer.json` vazio (exceto o próprio teste de regressão e textos de documentação em `resources/markdown`)
- [ ] `composer test`, `npm run build` e smoke verdes

**Status:** Aprovada pelo Lead (dev ausente)
**Data:** 2026-10-21
