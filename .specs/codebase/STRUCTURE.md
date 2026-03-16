# Estrutura do Projeto

**Raiz:** `/Users/giovanni/www/craftdb`

## Árvore de diretórios

```
craftdb/
├── app/
│   ├── Actions/
│   │   ├── Diagram/         # 15+ operações de diagrama
│   │   ├── Fortify/         # Auth: registro, OAuth, reset de senha
│   │   ├── Parse/           # ParseMySQLSchema, ParseLaravelMigration
│   │   └── Team/            # Operações de equipe
│   ├── Console/Commands/
│   ├── Enums/               # Role, ColumnType, RelationshipType, BillingCountry
│   ├── Exceptions/          # 6 exceções de domínio
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Pages/       # DiagramController aqui
│   │   └── Middleware/
│   ├── Listeners/
│   ├── Livewire/
│   │   ├── Forms/Diagram/   # ColumnForm, ImportForm
│   │   ├── Data/            # TableData, ColumnData (DTOs)
│   │   └── Contracts/       # PublicCanvas interface
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
│   │   └── canvas/          # InfiniteCanvas.js e sistema de canvas
│   └── views/
│       ├── components/      # Blade components (auth/, diagram/, layouts/, settings/, ui/)
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

**Propósito:** Criar diagramas a partir de SQL ou migrations Laravel
**Localização:**
- Actions: `app/Actions/Parse/ParseMySQLSchema.php`, `ParseLaravelMigration.php`
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
