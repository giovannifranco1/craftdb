# Estrutura do Projeto

**Raiz:** `/Users/giovanni/www/craftdb`

## Árvore de diretórios

```
craftdb/
├── app/
│   ├── Actions/
│   │   ├── Diagram/         # 15 operações: CreateNewDiagram, UpdateDiagram, DeleteDiagram,
│   │   │                    #   ImportToExistingDiagram, AddColumnToTable, DeleteColumnToTable,
│   │   │                    #   UpdateTable, DeleteTable, UpdateResizePositionTable,
│   │   │                    #   AddRelationship, AddRelationshipWithExistingForeignKey,
│   │   │                    #   AddRelationshipWithNewForeignKey, ExportDiagramToJson,
│   │   │                    #   ExportDiagramToLaravelMigration, GenerateMigrationZip
│   │   ├── Local/           # EnsureLocalUser (usuário local sem senha)
│   │   ├── Parse/           # BaseSQLParse (base), ParseMySQLSchema, ParsePOstgreSQLSchema,
│   │   │                    #   ParseSQLiteSchema, ParseLaravelMigration
│   │   └── Team/            # Workspaces: CreateNewTeam, RenameTeam, DeleteTeam
│   ├── Console/Commands/
│   ├── Enums/               # ColumnType, RelationshipType
│   ├── Exceptions/          # AuthorizationDiagramException
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Pages/       # DiagramController, LandingPageController
│   │   └── Middleware/
│   ├── Listeners/
│   ├── Livewire/
│   │   ├── BaseComponent.php     # Base abstrata: handler global de exceções de domínio
│   │   ├── CanvasComponent.php   # Base abstrata: operações do canvas interativo
│   │   ├── Forms/Diagram/        # ColumnForm.php, ImportForm.php (Livewire Form Objects)
│   │   ├── Data/                 # TableData.php, ColumnData.php (implementam Wireable)
│   │   └── Contracts/            # (vazio após a F3)
│   ├── Models/
│   │   ├── Team/            # Team (Workspace na UI)
│   │   ├── Scopes/          # TableAccessible
│   │   └── Concerns/        # HasTeams trait (ownedTeams, allTeams, firstTeam, ownsTeam)
│   ├── Notifications/
│   ├── Observers/           # DiagramObserver, TableObserver
│   ├── Policies/            # DiagramPolicy, TablePolicy, TeamPolicy
│   ├── Providers/
│   ├── Rules/Diagram/
│   ├── Support/             # Helpers
│   └── View/Components/
│       ├── Diagram/Canvas/
│       └── Layouts/
├── config/
│   └── craftdb.php          # Usuário local + APP_FORCE_HTTPS
├── database/
│   ├── migrations/          # 36 migrations
│   ├── factories/
│   └── seeders/
├── docs/                    # Documentação técnica existente
│   ├── DIAGRAM_CACHING.md
│   └── WORKSPACES.md        # Workspaces locais
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

### Workspaces (Team)

**Propósito:** Agrupar diagramas do usuário local em workspaces (UI "Workspace"; model `Team`, tabela `team`, `team_id` são o nome interno). Sem membros, convites ou papéis
**Localização:**
- Modelo: `app/Models/Team/Team.php` (`owner()`)
- Concern: `app/Models/Concerns/HasTeams.php`
- Página Livewire: `resources/views/components/settings/⚡workspaces.blade.php` (`settings/workspaces`, rota `workspaces.index`)
- Switcher: `resources/views/components/ui/⚡team-switcher/`
- Actions: `app/Actions/Team/` (`CreateNewTeam`, `RenameTeam`, `DeleteTeam`)
- Policy: `app/Policies/TeamPolicy.php`

### Usuário local

**Propósito:** Usuário único implícito (sem login, registro ou senha)
**Localização:**
- Middleware: `app/Http/Middleware/AuthenticateLocalUser.php` (login automático uma vez por sessão)
- Action: `app/Actions/Local/EnsureLocalUser.php`
- Comando: `app/Console/Commands/EnsureLocalUserCommand.php` (`php artisan craftdb:local-user`, executado no boot do container)
- Config: `config/craftdb.php` (`local_user.email`, `force_https`)

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
- Zero telemetria (F5): `tests/Feature/Telemetry/` (NoExternalServicesTest, NoExternalRequestsTest); fontes via `@fontsource` em `resources/css/app.css`

## Onde ficam as coisas

**Nova feature de diagrama:**
- Interface/UI: `resources/views/livewire/` + `resources/views/components/diagram/`
- Lógica de negócio: `app/Actions/Diagram/`
- Acesso a dados: `app/Models/` + migrations em `database/migrations/`
- Sem limites de plano: não adicionar checagens (removidas na F3)

**Nova integração externa:**
- Serviço/adapter: `app/Services/` (criar se não existir) ou diretamente no Controller
- Configuração: `config/services.php`
- Variáveis de ambiente: `.env.example`

**Docker / CraftDB Local:**
- Imagem e stack: `Dockerfile`, `.dockerignore`, `docker-compose.yml` (raiz)
- Boot do container: `docker/entrypoint.sh`; verificação end-to-end: `docker/smoke-test.sh`
- Usuário local: `app/Actions/Local/EnsureLocalUser.php` + comando `craftdb:local-user` (`app/Console/Commands/EnsureLocalUserCommand.php`)
- Configuração: `config/craftdb.php` (`local_user.*`, `force_https`)

**Nova rota:**
- Arquivo: `routes/web.php` (único arquivo de rotas)
- Middleware aplicado inline na definição da rota

## Diretórios especiais

**`app/Support/`:**
**Propósito:** Utilitários de negócio que não são Model, Service ou Action
**Exemplos:** `Helpers.php`

**`app/Livewire/Data/`:**
**Propósito:** DTOs usados para transferência de dados entre componentes Livewire
**Exemplos:** `TableData.php`, `ColumnData.php`

**`app/Livewire/Contracts/`:**
**Propósito:** Interfaces para componentes com comportamento compartilhado
**Exemplos:** nenhum no momento (`PublicCanvas` removido na F3)

**`docs/`:**
**Propósito:** Documentação técnica de features específicas do projeto
**Exemplos:** `DIAGRAM_CACHING.md`, `WORKSPACES.md`

**`.specs/codebase/`:**
**Propósito:** Contexto para agentes de IA (esta documentação)
