# Arquitetura

**Padrão identificado:** MVC com Actions (Command Pattern) + Livewire para reatividade

## Estrutura de alto nível

```
Request HTTP
    │
    ▼
Middleware (auth, verified, role:viewer|editor|owner, super.root)
    │
    ├── Controllers (HTTP) ──▶ Views Blade
    │       └── BillingController, DiagramController, AuthController
    │
    └── Livewire Components ──▶ Views Blade
            └── Actions ──▶ Models (Eloquent) ──▶ Database
                    └── Exceções de domínio
```

## Padrões identificados

### Actions (Command Pattern)

**Localização:** `app/Actions/`
**Propósito:** Encapsular operações de negócio com escopo único — separa lógica do Controller/Livewire
**Implementação:** Classes invocáveis com método `handle()` ou `__invoke()`
**Subdiretórios:**
- `app/Actions/Diagram/` — 13 operações:
  - `CreateNewDiagram` — cria diagrama com validação de limites e parse de SQL/migrations
  - `UpdateDiagram` — atualiza nome/slug do diagrama
  - `DeleteDiagram` — remove diagrama e dados relacionados
  - `ImportToExistingDiagram` — importa schema para diagrama existente
  - `AddColumnToTable` — adiciona coluna validando limite do plano
  - `DeleteColumnToTable` — remove coluna
  - `UpdateTable` — atualiza propriedades de tabela
  - `DeleteTable` — remove tabela
  - `UpdateResizePositionTable` — persiste posição/tamanho de tabela no canvas
  - `AddRelationship` — cria relacionamento genérico
  - `AddRelationshipWithExistingForeignKey` — relacionamento com FK existente
  - `AddRelationshipWithNewForeignKey` — relacionamento criando nova FK
  - `AddMemberToTeam` — adiciona membro ao time
- `app/Actions/Fortify/` — CreateNewUser, CreateUserFromOAuth, ResetUserPassword
- `app/Actions/Parse/` — 5 arquivos: `BaseSQLParse` (classe base), `ParseMySQLSchema`, `ParsePOstgreSQLSchema`, `ParseSQLiteSchema`, `ParseLaravelMigration`
- `app/Actions/Team/` — Operações de equipe
**Exemplo:** `app/Actions/Diagram/CreateNewDiagram.php` — valida nome, gera slug, faz parse de imports, verifica limites do plano

### Livewire Components

**Localização:** `app/Livewire/`
**Propósito:** Reatividade server-side sem JavaScript customizado — formulários, canvas interativo
**Implementação:** Classes que estendem `Component` do Livewire, com estados reativos e métodos
**Base classes:**
- `BaseComponent.php` — handler global de exceções de domínio para todos os componentes
- `CanvasComponent.php` — base para operações no canvas do diagrama
**Forms:** `app/Livewire/Forms/Diagram/` (ColumnForm, ImportForm) — Livewire Form Objects
**Data:** `app/Livewire/Data/` (TableData, ColumnData) — DTOs para transferência de dados

### Policies (Authorization)

**Localização:** `app/Policies/`
**Propósito:** Controle de acesso baseado em regras de negócio
**Arquivos:** DiagramPolicy, TablePolicy, TeamPolicy
**Uso:** Middleware `role:viewer|editor|owner` nas rotas de diagrama

### Observers (Side Effects)

**Localização:** `app/Observers/`
**Propósito:** Reagir a eventos de model sem poluir o model
**Arquivos:**
- `DiagramObserver.php` — invalidação de cache ao salvar/deletar diagrama
- `TableObserver.php` — efeitos colaterais em operações de tabela

### Exceções de Domínio

**Localização:** `app/Exceptions/`
**Propósito:** Comunicar violações de regras de negócio de forma expressiva
**Tipos:**
- `DiagramLimitExceededException` — plano free atingiu limite de diagramas
- `TableLimitExceededException` — diagrama atingiu limite de tabelas do plano
- `TableColumnLimitExceededException` — tabela atingiu limite de colunas do plano
- `AuthorizationDiagramException` — acesso não autorizado ao diagrama
- `PlanFeatureException` — funcionalidade não disponível no plano atual
- `EmailAlreadyRegisteredException` — email já cadastrado (OAuth com email existente)
**Integração:** Capturadas em `BaseComponent.php` para feedback visual ao usuário

### Enums

**Localização:** `app/Enums/`
**Propósito:** Tipos com domínio fechado
**Tipos:**
- `Role` — Editor, Viewer (roles de equipe)
- `ColumnType` — Tipos de dado de coluna (int, varchar, etc.)
- `RelationshipType` — Tipos de relacionamento (FK patterns)
- `BillingCountry` — USD (default) / BRL (Brasil) para precificação regional

## Fluxo de dados

### Criação de diagrama

```
POST /dashboard (Livewire)
    → CreateNewDiagram::handle()
        → Valida limites do plano (DiagramLimits::check())
        → Gera slug único
        → Se arquivo SQL: ParseMySQLSchema
        → Se migration Laravel: ParseLaravelMigration
        → Diagram::create() → tabelas, colunas, relacionamentos
        → Redireciona para /{team_id}/diagram/{slug}
```

### Fluxo de billing

```
GET /settings/billing/checkout/{plan}
    → BillingController::checkout()
        → Detecta país via cf-ipcountry header
        → Seleciona Price ID (USD ou BRL)
        → $user->newSubscription()->checkout()
        → Redireciona para Stripe Checkout

Stripe → POST /stripe/webhook
    → Cashier processa eventos de subscription
    → Atualiza status do plano no banco
```

### Compartilhamento público

```
POST /{team_id}/diagram/{slug}/make-public
    → DiagramController::makePublic()
        → Gera UUID token único
        → Diagram::update([public_token => uuid])

GET /diagram/public/{token}
    → DiagramController::showPublic()
        → Lookup via public_token (sem auth)
        → Renderiza canvas em modo read-only
```

### Cache de diagrama

```
Route model binding: {diagram:slug}
    → AppServiceProvider registra binding customizado
    → Cache hit: retorna do cache (5min prod, 60s local)
    → Cache miss: query no banco + armazena no cache
    → DiagramObserver invalida cache on save/delete
```

### Wireable DTOs

**Localização:** `app/Livewire/Data/`
**Propósito:** DTOs que implementam `Livewire\Wireable` — permitem serialização/desserialização eficiente entre requests sem query extra
**Implementação:**
```php
class TableData implements Wireable
{
    public function toLivewire(): array { /* serializa para array */ }
    public static function fromLivewire($value): self { /* reconstrói do array */ }
    public static function fromModel(Table $table): self { /* cria do model Eloquent */ }
}
```
**Arquivos:** `TableData.php`, `ColumnData.php`
**Benefício:** Livewire trafega o DTO serializado como array entre requests; `fromLivewire()` reconstrói o objeto sem re-consultar o banco

### Canvas JS (Alpine.js Stores)

**Localização:** `resources/js/canvas/`
**Propósito:** Gerenciar estado client-side do canvas interativo — posicionamento, drag, resize, linhas SVG de relacionamentos
**Orquestração:** `main.js` registra todos os stores como Alpine data components no evento `alpine:init`
**Stores registrados:**
- `runCanvas` → `_canvas.data.js` — coordenador principal, instancia `InfiniteCanvas`, gerencia zoom, modos de adição
- `runTable` → `_table.data.js` — posição/drag/resize de tabelas; z-index inicia em 1000
- `runArea` → `_area.data.js` — posição/drag/resize de áreas; z-index inicia em 500
- `runNote` → `_note.data.js` — posição/drag/resize de notas; z-index inicia em 600
- `runSvgLines` → `_svg-lines.js` — relacionamentos SVG com caches de performance (BoundingRect, SVG elements)
- `foreignKeysGraph` → `_foreign-keys-graph.js` — grafo D3.js de FK de uma tabela
- (sem nome Alpine) → `_diagram-graph.js` — grafo D3.js completo do diagrama

Detalhes completos em `.specs/codebase/CANVAS.md`

### AppServiceProvider

**Localização:** `app/Providers/AppServiceProvider.php`
**Configurações-chave:**
- **Route model binding customizado:** `{diagram:slug}` com cache de 5min (prod) / 60s (local) — busca diagrama pelo slug com cache automático
- **View namespacing:** registra `diagram::` como namespace de views para componentes Blade reutilizáveis
- **Trusted proxies:** configura para ler IPs reais atrás do Cloudflare

## Organização do código

**Abordagem:** Por camada com subdivisão por feature dentro de Actions

**Estrutura:**
- `app/Models/` — por entidade (flat)
- `app/Actions/` — por feature (Diagram/, Fortify/, Parse/, Team/)
- `app/Livewire/` — por componente com subpastas (Forms/, Data/, Contracts/)
- `app/Http/Controllers/` — por área (Pages/, sub-controllers)
- `app/Policies/` — por recurso protegido
- `app/Observers/` — por model observado

**Fronteiras de módulo:**
- Diagrama é o domínio central (Diagram, Table, Column, Relationship, Area, Note)
- Team é módulo independente com próprios models (Team, Membership, Role, Invitation)
- Billing é camada de infraestrutura sobre Cashier/Stripe
- Auth é delegado ao Fortify + Socialite (mínima customização)
