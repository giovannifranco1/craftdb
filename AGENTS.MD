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
| Integrar com APIs externas, webhooks | `.specs/codebase/INTEGRATIONS.md` | Stripe, OAuth, jobs, rotas |
| Implementar feature específica | `AGENTS.MD` + contexto da tarefa | Workflow RPI + PREVC |

**Regra de ouro:** Carregue 1–2 docs por vez. Para tarefas pesadas (ex.: refatorar um módulo), use ARCHITECTURE + STRUCTURE. Para testes, use TESTING + CONVENTIONS.

---

## Visão Geral do Projeto

**Tipo:** SaaS Web
**Domínio:** Database diagram designer / Entity-Relationship diagram tool
**Arquitetura:** Monolito Laravel com Livewire para reatividade

CraftDB é uma ferramenta SaaS para criação e visualização de diagramas de banco de dados. Permite criar diagramas do zero ou importando schemas SQL e migrations Laravel, com suporte a compartilhamento público, colaboração em equipe e modelo freemium com billing via Stripe.

---

## Arquitetura

**Padrão:** MVC + Actions (Command Pattern) + Livewire

- **Actions** em `app/Actions/` encapsulam toda lógica de negócio (não está nos Controllers ou Models)
- **Controllers** são finos — apenas coordenam request/response e delegam para Actions
- **Livewire** gerencia reatividade server-side sem JavaScript customizado (exceto canvas D3.js)
- **Exceções de domínio** em `app/Exceptions/` comunicam violações de regras — capturadas em `BaseComponent`
- **Observers** em `app/Observers/` cuidam de side effects (ex: invalidação de cache de diagrama)

Detalhes completos em `.specs/codebase/ARCHITECTURE.md`

---

## Stack

- **Backend:** Laravel 12 + PHP 8.2 + Eloquent ORM
- **Frontend:** Livewire 4 + Flux 2 + Tailwind CSS 4
- **Canvas:** D3.js 7 + `InfiniteCanvas.js` customizado
- **Billing:** Stripe via Laravel Cashier 16 (precificação regional USD/BRL)
- **Auth:** Fortify + Socialite (GitHub, Google) + 2FA

Detalhes completos em `.specs/codebase/STACK.md`

---

## Estrutura de Pastas

```
app/Actions/        → Toda lógica de negócio (por feature: Diagram/, Team/, Parse/, Fortify/)
app/Models/         → Entidades Eloquent + Team/ subpasta
app/Livewire/       → Componentes reativos + Forms/, Data/, Contracts/
app/Http/Controllers/ → Controllers finos (Pages/, BillingController, AuthController)
app/Support/        → DiagramLimits (limites por plano)
app/Enums/          → Role, ColumnType, RelationshipType, BillingCountry
app/Exceptions/     → 6 exceções de domínio
config/diagram.php  → Limites de features por plano
config/billing.php  → Preços e Price IDs do Stripe
resources/js/canvas/ → InfiniteCanvas.js (canvas D3.js)
routes/web.php      → Todas as rotas (arquivo único)
```

Detalhes completos em `.specs/codebase/STRUCTURE.md`

---

## Convenções

- **Actions** são a unidade de negócio — ao adicionar features, criar uma Action, não lógica inline em Controllers
- **Exceções de domínio** ao invés de retornar erros — `throw new TableLimitExceededException()`
- **Enums** para domínio fechado — Role, ColumnType, RelationshipType, BillingCountry
- **PascalCase** para classes, **camelCase** para métodos, **kebab-case** para rotas e views Blade
- **Pest** para testes com SQLite in-memory

Detalhes completos em `.specs/codebase/CONVENTIONS.md`

---

## Integrações Principais

- **Stripe / Cashier** — checkout, portal, webhooks automáticos em `/stripe/webhook`
- **GitHub + Google OAuth** — via Socialite, campos `*_id/*_token` no model User
- **Cloudflare** — header `CF-IPCountry` para detecção de região (USD vs BRL)
- **phpmyadmin/sql-parser** — import de schemas MySQL
- **AWS S3** — storage configurado

Detalhes completos em `.specs/codebase/INTEGRATIONS.md`

---

## Testes

- **Framework:** Pest 4 sobre PHPUnit
- **Banco em testes:** SQLite in-memory (phpunit.xml)
- **Cobertura atual:** Auth flows, Settings, Livewire components
- **Rodar:** `composer test` ou `./vendor/bin/pest`

Detalhes completos em `.specs/codebase/TESTING.md`

---

## Dicas para LLMs

1. **Consulte o Guia de Contexto** antes de carregar arquivos — escolha apenas o necessário
2. **Não carregue** vários docs de codebase ao mesmo tempo
3. **Novas features de diagrama** = nova Action em `app/Actions/Diagram/`
4. **Limites de plano** são verificados em `app/Support/DiagramLimits.php` + `config/diagram.php`
5. **Billing regional** = detectar via `CF-IPCountry` e usar Price ID correto (USD ou BRL)
6. **Ao modificar Models**, verifique Observers que podem reagir às mudanças
7. **Escreva testes** seguindo os padrões em `.specs/codebase/TESTING.md`
8. **Atualize este arquivo** sempre que padrões ou decisões arquiteturais mudarem

---

**Última atualização:** 2026-03-16
**Gerado por:** Skill Brownfield Mapping
