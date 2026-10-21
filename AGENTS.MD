# AGENTS.MD — CraftDB

> **Contexto otimizado para LLMs** — Este documento fornece o contexto essencial para agentes de IA trabalharem efetivamente neste projeto.

---

## Guia de Contexto: O Que Ler Quando

| Intenção | Arquivos a carregar | Observação |
|---|---|---|
| Visão geral, onboarding no projeto | `AGENTS.MD` (este arquivo) | Base load — sempre útil |
| Entender stack, dependências, versões | `.specs/codebase/STACK.md` | Extraído de composer.json, package.json |
| Entender arquitetura, padrões, fluxos | `.specs/codebase/ARCHITECTURE.md` | Padrões, camadas, fluxos de dados |
| Seguir convenções de código e naming | `.specs/codebase/CONVENTIONS.md` | Naming, estrutura de arquivos, imports |
| Encontrar onde X está no projeto | `.specs/codebase/STRUCTURE.md` | Árvore de diretórios, responsabilidades |
| Escrever ou entender testes | `.specs/codebase/TESTING.md` | Frameworks, padrões, como executar |
| Integrar com APIs externas, webhooks | `.specs/codebase/INTEGRATIONS.md` | MCP local, jobs, rotas |
| Trabalhar com canvas, Alpine stores, SVG | `.specs/codebase/CANVAS.md` | InfiniteCanvas, Alpine stores, eventos, z-index |
| Implementar feature específica | `AGENTS.MD` + contexto da tarefa | Workflow RPI + PREVC |

**Regra de ouro:** Carregue 1–2 docs por vez. Para tarefas pesadas (ex.: refatorar um módulo), use ARCHITECTURE + STRUCTURE. Para testes, use TESTING + CONVENTIONS.

---

## Visão Geral do Projeto

**Tipo:** Aplicação web open source (CraftDB Local) — roda via Docker, sem login
**Domínio:** Database diagram designer / Entity-Relationship diagram tool
**Arquitetura:** Monolito Laravel com Livewire para reatividade

CraftDB Local é uma ferramenta open source para criação e visualização de diagramas de banco de dados, executada localmente via Docker. Permite criar diagramas do zero ou importando schemas SQL e migrations Laravel, com workspaces (times). Não há login: um usuário local único é autenticado automaticamente (middleware `AuthenticateLocalUser`). Não há billing, planos, limites nem links públicos (removidos na F3): todas as funcionalidades (import, export, grafo, áreas, notas) estão sempre disponíveis. Não há landing page (removida na F7): `/` leva ao dashboard e a página `/support` (Apoiar) reúne os links de apoio ao projeto.

---

## Arquitetura

**Padrão:** MVC + Actions (Command Pattern) + Livewire

- **Actions** em `app/Actions/` encapsulam toda lógica de negócio (não está nos Controllers ou Models)
- **Controllers** são finos — apenas coordenam request/response e delegam para Actions
- **Livewire** gerencia reatividade server-side; **Alpine.js** gerencia estado client-side do canvas
- **Canvas:** `InfiniteCanvas.js` (HTML5 Canvas 2D) + 6 Alpine stores + D3.js para grafos de relacionamentos
- **Exceções de domínio** em `app/Exceptions/` comunicam violações de regras — capturadas em `BaseComponent`
- **Observers** em `app/Observers/` cuidam de side effects (ex: invalidação de cache de diagrama)

Detalhes completos em `.specs/codebase/ARCHITECTURE.md`

---

## Stack

- **Backend:** Laravel 13 + PHP 8.2+ (8.4 na imagem Docker) + Eloquent ORM
- **Local:** Docker Compose (FrankenPHP + MySQL 8.4) — `docker compose up -d`, ver `docs/README.md`
- **Frontend:** Livewire 4 + Flux 2 + Tailwind CSS 4
- **Canvas:** D3.js 7 + `InfiniteCanvas.js` customizado
- **Auth:** Sem login — usuário local autenticado automaticamente (`AuthenticateLocalUser` + `EnsureLocalUser`); o MCP age como esse mesmo usuário (sem token)

Detalhes completos em `.specs/codebase/STACK.md`

---

## Estrutura de Pastas

```
app/Actions/        → Toda lógica de negócio (por feature: Diagram/, Team/ [workspaces], Parse/, Local/)
app/Models/         → Entidades Eloquent + Team/ subpasta (`Team` = Workspace na UI)
app/Livewire/       → Componentes reativos + Forms/, Data/, Contracts/
app/Http/Controllers/ → Controllers finos (Pages/)
app/Support/        → Helpers
resources/views/pages/⚡support/ → Página Apoiar (`/support`; links em `config('craftdb.support')`)
app/Enums/          → ColumnType, RelationshipType
app/Exceptions/     → AuthorizationDiagramException (única exceção de domínio)
resources/js/canvas/ → InfiniteCanvas.js + 6 Alpine stores + D3.js (ver CANVAS.md)
routes/web.php      → Todas as rotas (arquivo único)
config/craftdb.php  → Usuário local + flag APP_FORCE_HTTPS (CraftDB Local)
Dockerfile, docker-compose.yml, docker/ → Stack local (entrypoint + smoke test)
```

Detalhes completos em `.specs/codebase/STRUCTURE.md`

---

## Convenções

- **Actions** são a unidade de negócio — ao adicionar features, criar uma Action, não lógica inline em Controllers
- **Exceções de domínio** ao invés de retornar erros — `throw new AuthorizationDiagramException()`
- **Enums** para domínio fechado — ColumnType, RelationshipType
- **PascalCase** para classes, **camelCase** para métodos, **kebab-case** para rotas e views Blade
- **Pest** para testes com SQLite in-memory

Detalhes completos em `.specs/codebase/CONVENTIONS.md`

---

## Integrações Principais

- **phpmyadmin/sql-parser** — import de schemas MySQL
- **Zero telemetria (F5)** — sem Nightwatch, Resend/Postmark/SES, S3/AWS nem Google Fonts; fontes self-hosted via `@fontsource` (bundladas pelo Vite). Guardado por `tests/Feature/Telemetry/` e pelo check ZT-09 do `docker/smoke-test.sh`
- **MCP local (F6)** — sem Passport/OAuth/token. HTTP em `http://localhost:8080/mcp/craftdb` (POST sem auth; protegido pelo bind `127.0.0.1`, pelo middleware `EnsureLocalMcpOrigin` — Origin ausente ou localhost/127.0.0.1/[::1]/host do `APP_URL`, senão 403 — e por `throttle:mcp`). stdio: `php artisan mcp:start craftdb` (no Docker: `docker compose exec -T app php artisan mcp:start craftdb`). `McpAccess::user()` = `Auth::user()` ou o usuário local (`EnsureLocalUser`); checagens de posse sempre valem. Nomes `list_teams`/`team_id` mantidos (contrato). `.cursor/mcp.json` só tem a URL local

Detalhes completos em `.specs/codebase/INTEGRATIONS.md`

---

## Testes

- **Framework:** Pest 4 sobre PHPUnit
- **Banco em testes:** SQLite in-memory (phpunit.xml)
- **Cobertura atual:** Auth/usuário local, Settings, Livewire components, regressões da F3 (`tests/Feature/Plans/`, 263 testes)
- **Rodar:** `composer test` ou `./vendor/bin/pest`
- **Stack Docker:** `bash docker/smoke-test.sh` (apaga os volumes do stack)

Detalhes completos em `.specs/codebase/TESTING.md`

---

## Dicas para LLMs

1. **Consulte o Guia de Contexto** antes de carregar arquivos — escolha apenas o necessário
2. **Não carregue** vários docs de codebase ao mesmo tempo
3. **Novas features de diagrama** = nova Action em `app/Actions/Diagram/`
4. **Sem planos nem limites** — a F3 removeu billing, limites e gates premium; não reintroduza checagens de plano nas Actions/MCP
5. **Ao modificar Models**, verifique Observers que podem reagir às mudanças
6. **Ao tocar em canvas JS** (Alpine stores, SVG lines, InfiniteCanvas), carregue `.specs/codebase/CANVAS.md`
7. **Escreva testes** seguindo os padrões em `.specs/codebase/TESTING.md`
8. **Atualize este arquivo** sempre que padrões ou decisões arquiteturais mudarem

---

**Última atualização:** 2026-10-21 (F7 — M1 concluído)
**Gerado por:** Skill Brownfield Mapping + revisão manual
