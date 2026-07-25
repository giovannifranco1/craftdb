# Estrutura do Projeto

**Raiz:** `/Users/giovanni/www/craftdb`

## Árvore de diretórios

```
craftdb/
├── app/
│   ├── Actions/
│   │   ├── Diagram/         # 13 operações: CreateNewDiagram, UpdateDiagram, DeleteDiagram,
│   │   │                    #   ImportToExistingDiagram, AddColumnToTable, DeleteColumnToTable,
│   │   │                    #   UpdateTable, DeleteTable, UpdateResizePositionTable,
│   │   │                    #   AddRelationship, AddRelationshipWithExistingForeignKey,
│   │   │                    #   AddRelationshipWithNewForeignKey, AddMemberToTeam
│   │   ├── Fortify/         # Auth: CreateNewUser, CreateUserFromOAuth, ResetUserPassword
│   │   ├── Parse/           # BaseSQLParse (base), ParseMySQLSchema, ParsePOstgreSQLSchema,
│   │   │                    #   ParseSQLiteSchema, ParseLaravelMigration
│   │   └── Team/            # Operações de equipe
│   ├── Console/Commands/
│   ├── Enums/               # Role, ColumnType, RelationshipType, BillingCountry
│   ├── Exceptions/          # DiagramLimitExceededException, TableLimitExceededException,
│   │                        #   TableColumnLimitExceededException, AuthorizationDiagramException,
│   │                        #   PlanFeatureException, EmailAlreadyRegisteredException
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Pages/       # DiagramController, LandingPageController
│   │   │   ├── BillingController.php
│   │   │   ├── AuthController.php
│   │   │   ├── InvitationController.php
│   │   │   └── FeedbackController.php
│   │   └── Middleware/
│   ├── Listeners/
│   ├── Livewire/
│   │   ├── BaseComponent.php     # Base abstrata: handler global de exceções de domínio
│   │   ├── CanvasComponent.php   # Base abstrata: operações do canvas interativo
│   │   ├── MyFavorites.php       # Componente: lista de diagramas favoritos
│   │   ├── Forms/Diagram/        # ColumnForm.php, ImportForm.php (Livewire Form Objects)
│   │   ├── Data/                 # TableData.php, ColumnData.php (implementam Wireable)
│   │   ├── Actions/              # Logout.php (Livewire action)
│   │   └── Contracts/            # PublicCanvas.php (interface para canvas read-only)
│   ├── Models/
│   │   ├── Team/            # Team, Membership, Role, Invitation
│   │   ├── Scopes/          # TableAccessible
│   │   └── Concerns/        # HasTeams trait
│   ├── Notifications/
│   ├── Observers/           # DiagramObserver, TableObserver
│   ├── Policies/            # DiagramPolicy, TablePolicy, TeamPolicy
│   ├── Providers/
│   ├── Rules/Diagram/
│   ├── Support/             # DiagramLimits
│   └── View/Components/
│       ├── Diagram/Canvas/
│       └── Layouts/
├── config/
│   ├── billing.php          # Planos, preços, Stripe Price IDs
│   ├── diagram.php          # Limites por plano (free/pro/team)
│   └── teams.php            # Configuração de equipes e roles
├── database/
│   ├── migrations/          # 26 migrations
│   ├── factories/
│   └── seeders/
├── docs/                    # Documentação técnica existente
│   ├── DIAGRAM_CACHING.md
│   ├── PUBLIC_SHARING.md
│   └── TEAM_MANAGEMENT.md
├── lang/                    # Internacionalização
├── public/
├── resources/
│   ├── css/
│   ├── js/
│   │   ├── app.js           # Entry point principal do Vite
│   │   └── canvas/
│   │       ├── main.js      # Registra Alpine data stores no alpine:init
│   │       ├── helpers.js   # Utilitários compartilhados do canvas
│   │       ├── classes/
│   │       │   └── InfiniteCanvas.js  # HTML5 Canvas 2D: pan/zoom/touch/grid
│   │       └── data/
│   │           ├── _canvas.data.js         # runCanvas — coordenador principal
│   │           ├── _table.data.js          # runTable — drag/resize de tabelas
│   │           ├── _area.data.js           # runArea — drag/resize de áreas
│   │           ├── _note.data.js           # runNote — drag/resize de notas
│   │           ├── _svg-lines.js           # runSvgLines — linhas SVG de relacionamentos
│   │           ├── _foreign-keys-graph.js  # foreignKeysGraph — grafo D3 de FK por tabela
│   │           └── _diagram-graph.js       # grafo D3 completo do diagrama
│   └── views/
│       ├── components/      # Blade components (auth/, diagram/, landing-page/, layouts/, settings/, ui/)
│       ├── livewire/        # Views de componentes Livewire
│       └── pages/           # Views de páginas (landing, dashboard, etc.)
├── routes/
│   └── web.php              # Único arquivo de rotas
├── storage/
├── tests/
│   ├── Feature/
│   │   ├── Auth/            # Auth flow tests
│   │   ├── Settings/        # Settings tests
│   │   └── Actions/Parse/   # Fixtures para parser
│   └── Unit/
├── .specs/codebase/         # Esta documentação
├── composer.json
├── package.json
├── phpunit.xml
├── tailwind.config.js
└── vite.config.js
```

## Organização por módulo/área

### Diagrama (domínio central)

**Propósito:** Criação, edição e visualização de diagramas de banco de dados
**Localização:**
- Modelos: `app/Models/Diagram.php`, `Table.php`, `Column.php`, `Relationship.php`, `Area.php`, `Note.php`
- Actions: `app/Actions/Diagram/`
- Controller: `app/Http/Controllers/Pages/DiagramController.php`
- Canvas JS: `resources/js/canvas/`
- Views: `resources/views/pages/diagram/`, `resources/views/components/diagram/`

**Arquivos-chave:**
- `app/Actions/Diagram/CreateNewDiagram.php` — entrada principal para novos diagramas
- `app/Actions/Diagram/AddColumnToTable.php`, `AddRelationship.php`
- `app/Support/DiagramLimits.php` — calcula limites por plano

### Equipes

**Propósito:** Colaboração multi-usuário em diagramas
**Localização:**
- Modelos: `app/Models/Team/` (Team, Membership, Role, Invitation)
- Concern: `app/Models/Concerns/HasTeams.php`
- Actions: `app/Actions/Team/`
- Policy: `app/Policies/TeamPolicy.php`
- Config: `config/teams.php`

### Autenticação

**Propósito:** Login, registro, OAuth, 2FA
**Localização:**
- Actions: `app/Actions/Fortify/` (CreateNewUser, CreateUserFromOAuth, ResetUserPassword)
- Controller: `app/Http/Controllers/AuthController.php`
- Provider: `app/Providers/FortifyServiceProvider.php`
- Views: `resources/views/components/auth/`

### Billing

**Propósito:** Assinaturas, planos, pagamentos Stripe
**Localização:**
- Controller: `app/Http/Controllers/BillingController.php`
- Config: `config/billing.php`
- Enum: `app/Enums/BillingCountry.php`
- Views: `resources/views/pages/settings/billing.blade.php`

### Importação de Schema

**Propósito:** Criar diagramas a partir de SQL (MySQL, PostgreSQL, SQLite) ou migrations Laravel
**Localização:**
- Actions: `app/Actions/Parse/` — 5 arquivos
  - `BaseSQLParse.php` — classe base com lógica compartilhada entre parsers SQL
  - `ParseMySQLSchema.php` — usa phpmyadmin/sql-parser
  - `ParsePOstgreSQLSchema.php` — parser customizado para PostgreSQL (nota: typo no nome do arquivo)
  - `ParseSQLiteSchema.php` — parser customizado para SQLite
  - `ParseLaravelMigration.php` — parser customizado para migrations Laravel
- Form: `app/Livewire/Forms/Diagram/ImportForm.php`
- Fixtures de teste: `tests/Feature/Actions/Parse/`

## Onde ficam as coisas

**Nova feature de diagrama:**
- Interface/UI: `resources/views/livewire/` + `resources/views/components/diagram/`
- Lógica de negócio: `app/Actions/Diagram/`
- Acesso a dados: `app/Models/` + migrations em `database/migrations/`
- Configuração: `config/diagram.php` (se envolver limites de plano)

**Nova integração externa:**
- Serviço/adapter: `app/Services/` (criar se não existir) ou diretamente no Controller
- Configuração: `config/services.php` + `config/billing.php`
- Variáveis de ambiente: `.env.example`

**Nova rota:**
- Arquivo: `routes/web.php` (único arquivo de rotas)
- Middleware aplicado inline na definição da rota

## Diretórios especiais

**`app/Support/`:**
**Propósito:** Utilitários de negócio que não são Model, Service ou Action
**Exemplos:** `DiagramLimits.php` — centraliza cálculo de limites por plano

**`app/Livewire/Data/`:**
**Propósito:** DTOs usados para transferência de dados entre componentes Livewire
**Exemplos:** `TableData.php`, `ColumnData.php`

**`app/Livewire/Contracts/`:**
**Propósito:** Interfaces para componentes com comportamento compartilhado
**Exemplos:** `PublicCanvas` — interface para canvas em modo público (read-only)

**`docs/`:**
**Propósito:** Documentação técnica de features específicas do projeto
**Exemplos:** `DIAGRAM_CACHING.md`, `PUBLIC_SHARING.md`, `TEAM_MANAGEMENT.md`

**`.specs/codebase/`:**
**Propósito:** Contexto para agentes de IA (esta documentação)
