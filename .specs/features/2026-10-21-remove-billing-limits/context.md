# Contexto — F3 Remoção de billing, limites e links públicos

**Origem:** ROADMAP M1/F3 · pivot total (STATE.md) · regra de commit: 1 commit por fase, sem push
**Data:** 2026-10-21
**Escopo:** Large · defaults do processo (subagents + two-stage review)
**Aprovações:** dev ausente — decisões tomadas pelo **Lead** seguindo as opções recomendadas (STATE.md, 2026-10-21)

## Decisões (Lead, dev ausente)

| # | Decisão | Alternativas descartadas |
|---|---|---|
| D1 | **Remover Cashier/Stripe por completo**: pacote, config, `Billable`, rotas (incl. as do Cashier), `BillingController`, tela de billing, links e badge de plano. Migration nova apaga `subscriptions`, `subscription_items` e colunas `stripe_id`, `pm_type`, `pm_last_four`, `trial_ends_at` | Manter Cashier inativo |
| D2 | **Sem limites**: apagar `DiagramLimits`, limites de `config/diagram.php`, `DiagramLimitExceededException`, `TableLimitExceededException`, `TableColumnLimitExceededException`, badge `⚡limit` e textos "X / limite" | Trocar o plano do usuário local para "ilimitado" (Pro/Team ainda têm teto de 100) |
| D3 | **Sem recursos premium**: importação, exportação (JSON/migration), grafo, áreas e notas sempre liberados; apagar `PlanFeatureException` (depois de trocar o uso de token/time no MCP), `pro-feature-modal`, `config/pro-features.php`, store Alpine e listeners | Manter gates com `true` fixo |
| D4 | **Sem links públicos**: apagar rota `/diagram/public/{token}`, `showPublic`, componente `⚡share` (só a parte pública), canvas público (layout, componentes, `canvas-public.css`, `PublicCanvas`), `DiagramPolicy::share`, métodos `isPublic/makePublic/makePrivate/getPublicUrl`; migration apaga `diagram.public_token` (índice único antes). O dropdown "Compartilhar" mantém "copiar link interno" | Manter links públicos locais |
| D5 | **Sem preço regional**: apagar `BillingCountry`, `SetHeaders` (só servia ao billing), `config('app.cloudflare_proxy')`, `CLOUDFLARE_PROXY`, rota `/debug-cf`, `Helpers::formatPriceAmount` | — |
| D6 | **MCP sem planos**: `McpAccess` deixa de exigir premium e limite; `ListDiagramsTool` lista todos os diagramas dos times do token; `ListTeamsTool` e instruções do servidor sem Free/Pro/Team; a checagem token↔time (`assertTokenAllowsTeam`) passa a lançar `AuthorizationException` (já tratada em `CraftdbCallTool`) | Deixar MCP para a F6 (ficaria inutilizável até lá) |
| D7 | **Criar time liberado**: `TeamPolicy::create` → `true` (o resto de times é F4) | — |

## Decisões do agente

| # | Decisão | Motivo |
|---|---|---|
| A1 | Landing (incl. seção de preços e `config/landingpage.php`) fica até a F7 | Escopo F7; não usa rotas de billing |
| A2 | `docs/README.md`: remover só as partes de Stripe/billing da seção "Deploy em Produção"; a seção inteira (SaaS) é revista na F5/F7 | Manter F3 focada |
| A3 | `docs/PUBLIC_SHARING.md` é apagado (já estava obsoleto) | Funcionalidade removida |
| A4 | Exports deixam de ter bypass `environment('testing')` — o gate some de vez | Sem gate, sem bypass |
| A5 | Dead code achado no mapeamento e ligado à F3 sai junto: `Diagram::TABLE_LIMIT`, `UserFactory::withActiveProSubscription`, `BillingSubscriptionFactory`, `User::clearSubscriptionCaches` (+ chamada em `TestCase::setUp`), `BILLING_*_PRICE` no `.env.example`, item do STATE "invited members inherit" | Tudo vinculado a planos |
| A6 | `cache()->forget('diagram')` em `DiagramController::show` (chave sem uso) fica — não é F3 | Fora de escopo, registrado no STATE |

## Fatos do codebase (mapeamento 2026-10-21)

- MCP hoje é inutilizável para o usuário local: `McpAccess::assertWorkspacePremium` em quase toda tool; `list_diagrams` vazio
- `PlanFeatureException` também é usada em `McpAccess.php:131` (token sem permissão para o time) — não-billing
- Cashier registra sozinho `POST stripe/webhook` e `GET stripe/payment/{id}`
- `public_token` está na migration original de `diagram` com índice único
- `TeamPolicy::create` hoje bloqueia criação de time para quem não é Team plan (usuário local)
- Testes com assinatura Cashier: `McpToolsAuthorizationTest` (2), `ExportDiagramToJsonTest` (2 + 1 de 403), `ExportDiagramToLaravelMigrationTest` (`isPro` mock + 3), `limit.test.php`, `area-list.test.php`, `note-list.test.php`, `table-list.test.php` (limite)
- `EnsureValidCurrentTeam.php:53` isenta rotas `billing.*`
- `resources/js/canvas/data/_svg-lines.js:65` checa o nome do componente público de tabela
