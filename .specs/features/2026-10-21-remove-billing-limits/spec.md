# F3 — Remoção de billing, limites e links públicos — Especificação

## Problema

O CraftDB Local é gratuito e roda só na máquina do dev, mas ainda carrega o modelo freemium do SaaS: plano Free com 3 diagramas, 10 tabelas e 50 colunas, recursos "Pro" bloqueados (importar, exportar, grafo, áreas, notas), criação de time bloqueada, MCP inutilizável, tela de assinatura Stripe e links públicos que dependiam de um servidor na internet.

## Objetivos

- [ ] Todo recurso do app disponível sem limite para o usuário local
- [ ] Nenhuma dependência, rota, tela, tabela ou coluna de billing, plano ou link público
- [ ] MCP funcional para o usuário local (listar e editar diagramas)
- [ ] Suite verde e smoke Docker verde

## Fora do Escopo

| Item | Motivo |
|---|---|
| Landing page, seção de preços, `config/landingpage.php` | F7 |
| Renomear Team → Workspace, telas de time além do `create` | F4 |
| Nightwatch, Resend, S3, fontes, seção "Deploy em Produção" inteira | F5 |
| Passport, chaves OAuth no Docker, `route:cache` | F6 |
| `cache()->forget('diagram')` sem uso em `DiagramController` | Não é F3 (STATE) |

---

## Glossário

| Termo | Definição canônica | Fonte |
|---|---|---|
| Recurso premium | Qualquer recurso antes restrito a Pro/Team (importar, exportar, grafo, áreas, notas, criar time, MCP) — após F3, não existe mais o conceito | D3 |
| Limite | Teto de diagramas, tabelas ou colunas por plano — após F3, não existe | D2 |
| Link público | URL `/diagram/public/{token}` que exibia o diagrama sem login — removida | D4 |
| Link interno | URL do diagrama dentro do app (`diagram.show`) — continua | D4 |

---

## User Stories

### P1: Tudo liberado, sem limites ⭐ MVP

**User Story**: Como usuário local, quero criar quantos diagramas, tabelas e colunas precisar, para modelar bancos reais sem barreiras artificiais.

**Critérios de Aceite**:

1. QUANDO o usuário cria um diagrama, independentemente de quantos já tem ENTÃO o sistema DEVE criá-lo sem erro de limite (FREE-01)
2. QUANDO o usuário adiciona tabelas (canvas, relacionamento n:n, importação SQL/migration) ou colunas (canvas, editor lateral) além dos antigos tetos (10/30/100 tabelas, 50/100 colunas) ENTÃO o sistema DEVE aceitar (FREE-02)
3. QUANDO qualquer tela do app é renderizada ENTÃO NÃO DEVE haver contador "X / limite", badge de plano (Free/Pro/Team), modal de upgrade ou texto pedindo upgrade (FREE-03)
4. QUANDO o código é inspecionado ENTÃO NÃO DEVEM existir `DiagramLimits`, chaves de limite em `config/diagram.php`, `DiagramLimitExceededException`, `TableLimitExceededException`, `TableColumnLimitExceededException`, componente `⚡limit` (FREE-04)

**Teste Independente**: Pest cria 5 diagramas e 120 tabelas com 120 colunas numa tabela, sem exceção.

---

### P1: Recursos antes premium liberados ⭐ MVP

**User Story**: Como usuário local, quero importar, exportar, ver o grafo e usar áreas e notas, sem plano pago.

**Critérios de Aceite**:

1. QUANDO o usuário importa SQL/migration ao criar diagrama ou num diagrama existente ENTÃO o sistema DEVE importar (FREE-05)
2. QUANDO o usuário exporta JSON ou migrations Laravel (UI e rotas `diagram.export-*`) ENTÃO o sistema DEVE exportar (FREE-06)
3. QUANDO o usuário abre o grafo, cria área ou cria nota ENTÃO o sistema DEVE permitir (FREE-07)
4. QUANDO o usuário cria um time ENTÃO o sistema DEVE permitir (`TeamPolicy::create` sempre `true`) (FREE-08)
5. QUANDO o código é inspecionado ENTÃO NÃO DEVEM existir `PlanFeatureException`, `hasDiagramPremiumAccess`, `hasTeamWorkspacePremiumAccess`, `isPro`, `isTeamPlan`, `canCreateTeam`, `pro-feature-modal`, `config/pro-features.php`, store/listeners `pro-feature` (FREE-09)

**Teste Independente**: Pest como usuário local sem assinatura: export JSON 200, import cria tabelas, `area-list`/`note-list` criam itens, `TeamPolicy::create` true.

---

### P1: Remover billing ⭐ MVP

**User Story**: Como mantenedor, quero tirar Stripe/Cashier do projeto, já que o produto é gratuito.

**Critérios de Aceite**:

1. QUANDO a aplicação sobe ENTÃO NÃO DEVEM existir rotas `billing.*`, `cashier.webhook`, `cashier.payment` nem a tela de billing (FREE-10)
2. QUANDO o `composer.json` é inspecionado ENTÃO `laravel/cashier` NÃO DEVE estar presente e o app DEVE subir sem ele (FREE-11)
3. QUANDO o usuário abre Configurações ou os menus ENTÃO NÃO DEVE haver link "Billing/Assinatura" (FREE-12)
4. QUANDO o código é inspecionado ENTÃO NÃO DEVEM existir `BillingController`, `config/billing.php`, `config/cashier.php`, `BillingCountry`, `SetHeaders`, `config('app.cloudflare_proxy')`, rota `/debug-cf`, `Helpers::formatPriceAmount`, variáveis `STRIPE_*`, `BILLING_*`, `CLOUDFLARE_PROXY` no `.env.example`, `BillingSubscriptionFactory`, `withActiveProSubscription`, `clearSubscriptionCaches` (FREE-13)

**Teste Independente**: `route:list` sem as rotas; `composer show laravel/cashier` falha.

---

### P1: Remover links públicos ⭐ MVP

**User Story**: Como usuário local, não quero opções de "tornar público" que não funcionam fora da minha máquina.

**Critérios de Aceite**:

1. QUANDO a aplicação sobe ENTÃO NÃO DEVE existir a rota `diagram.public` (FREE-14)
2. QUANDO o usuário abre o menu "Compartilhar" de um diagrama ENTÃO DEVE ver só "copiar link interno" (e o slug), sem "tornar público"/"revogar" (FREE-15)
3. QUANDO o código é inspecionado ENTÃO NÃO DEVEM existir `showPublic`, `DiagramPolicy::share`, `Diagram::isPublic/makePublic/makePrivate/getPublicUrl`, canvas público (`layouts.canvas.public`, `CanvasPublic`, `components/diagram/public/**`, `canvas-public.css`, `PublicCanvas`), `docs/PUBLIC_SHARING.md`, nem referência ao componente público em `resources/js` (FREE-16)

**Teste Independente**: `Route::has('diagram.public')` falso; GET do diagrama mostra "copiar link" e não mostra "público".

---

### P1: Schema sem billing e sem token público ⭐ MVP

**Critérios de Aceite**:

1. QUANDO as migrations rodam ENTÃO NÃO DEVEM existir as tabelas `subscriptions`, `subscription_items` nem as colunas `users.stripe_id`, `users.pm_type`, `users.pm_last_four`, `users.trial_ends_at`, `diagram.public_token` (índices removidos antes das colunas; funciona em SQLite e MySQL) (FREE-17)
2. QUANDO a migration roda sobre um banco existente (volume Docker) ENTÃO diagramas, tabelas, colunas e times DEVEM ser preservados (FREE-18)

---

### P1: MCP sem planos ⭐ MVP

**User Story**: Como usuário local, quero usar o MCP com agentes de IA sem plano pago.

**Critérios de Aceite**:

1. QUANDO uma tool MCP é chamada para um time permitido pelo token ENTÃO o sistema NÃO DEVE exigir plano nem limite (FREE-19)
2. QUANDO `list_diagrams` é chamada ENTÃO DEVE listar os diagramas de todos os times permitidos pelo token (FREE-20)
3. QUANDO `list_teams` é chamada ou as instruções do servidor são lidas ENTÃO NÃO DEVE haver menção a Free/Pro/Team, limites ou upgrade (FREE-21)
4. QUANDO o token não tem permissão para o time ENTÃO o sistema DEVE continuar negando, agora com `AuthorizationException` (FREE-22)
5. QUANDO a tela `mcp.authorize` é exibida ENTÃO NÃO DEVE mostrar badge de plano (FREE-23)

**Teste Independente**: `McpToolsAuthorizationTest` sem assinatura: lista diagramas do usuário; token sem o time → negado.

---

### P2: Testes e docs alinhados

1. QUANDO a suite roda ENTÃO NÃO DEVEM existir testes de limite, plano, assinatura ou link público (incl. o caso `share` de `DiagramOwnershipTest`); testes que criavam assinatura para liberar recurso DEVEM funcionar sem ela (FREE-24)
2. QUANDO `composer test` e `bash docker/smoke-test.sh` rodam ENTÃO DEVEM passar (FREE-25)

---

## Casos Limite

- QUANDO um diagrama do banco antigo tinha `public_token` preenchido ENTÃO a migration apaga a coluna e o diagrama continua acessível pelo link interno (FREE-18)
- QUANDO o usuário tinha assinatura antiga (`subscriptions`) ENTÃO a tabela some sem afetar o usuário (FREE-18)
- QUANDO uma tela/teste chamava `hasTeamWorkspacePremiumAccess()` sem argumento ENTÃO a chamada some junto com o gate (FREE-09)

---

## Rastreabilidade de Requisitos

| ID | Story | Fase | Status |
|---|---|---|---|
| FREE-01 | Sem limites | Execute | Verified |
| FREE-02 | Sem limites | Execute | Verified |
| FREE-03 | Sem limites | Execute | Verified |
| FREE-04 | Sem limites | Execute | Verified |
| FREE-05 | Premium liberado | Execute | Verified |
| FREE-06 | Premium liberado | Execute | Verified |
| FREE-07 | Premium liberado | Execute | Verified |
| FREE-08 | Premium liberado | Execute | Verified |
| FREE-09 | Premium liberado | Execute | Verified |
| FREE-10 | Billing | Execute | Verified |
| FREE-11 | Billing | Execute | Verified |
| FREE-12 | Billing | Execute | Verified |
| FREE-13 | Billing | Execute | Verified |
| FREE-14 | Links públicos | Execute | Verified |
| FREE-15 | Links públicos | Execute | Verified |
| FREE-16 | Links públicos | Execute | Verified |
| FREE-17 | Schema | Execute | Verified |
| FREE-18 | Schema | Execute | Verified |
| FREE-19 | MCP | Execute | Verified |
| FREE-20 | MCP | Execute | Verified |
| FREE-21 | MCP | Execute | Verified |
| FREE-22 | MCP | Execute | Verified |
| FREE-23 | MCP | Execute | Verified |
| FREE-24 | Testes/docs | Execute | Verified |
| FREE-25 | Testes/docs | Execute | Verified |

**Cobertura:** 25 total, 25 mapeados, 25 verificados (Pest + MySQL sobre volume existente + smoke)

---

## Critérios de Sucesso

- [ ] Usuário local cria diagramas/tabelas/colunas sem teto, importa, exporta, usa grafo, áreas, notas e cria time
- [ ] `grep -rniE "cashier|stripe|subscri|\bisPro\b|isTeamPlan|premium|DiagramLimits|public_token|makePublic|BillingCountry|cf-ipcountry" app bootstrap config routes resources database/factories lang | grep -vE "resources/views/pages/landing-page|config/landingpage.php|lang/[^/]+/landing-page.php"` vazio (as exclusões são F7; `tests/` fica fora porque os testes de guarda afirmam a ausência desses nomes). Strings de plano/limite/billing/link público em `lang/*.json` são limpas na F3
- [ ] `composer test` e smoke Docker verdes

---

## Self-Review

- [x] Placeholder scan — sem TBD/TODO
- [x] Consistência — D1–D7 e A1–A6 refletidos
- [x] Scope — landing, workspaces, telemetria e Passport fora
- [x] Ambiguidade — o que fica no "Compartilhar", destino do erro token↔time no MCP, teto Pro/Team
- [x] YAGNI — nenhuma flag de modo

**Reviewer:** spec-document-reviewer — Approved; grep de sucesso corrigido (`\bisPro\b`, exclusões F7 de `lang/*/landing-page.php`)
**Status:** Aprovada pelo Lead (dev ausente), 2026-10-21
**Data:** 2026-10-21
