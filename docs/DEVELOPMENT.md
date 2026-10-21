# CraftDB: development guide

Technical notes for people working on the CraftDB code. To simply run the app, see the [README](../README.md).

## Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2+ (8.4 in the Docker image), Laravel 13, Eloquent |
| Reactivity | Livewire 4 + Flux 2 |
| Frontend | Alpine.js, Tailwind CSS 4, Vite 7 |
| Canvas | HTML5 Canvas 2D (`InfiniteCanvas`) + SVG relationship lines + D3.js 7 |
| MCP | `laravel/mcp` |
| SQL import | `phpmyadmin/sql-parser` |
| Tests | Pest 4 (SQLite in memory) |
| Quality | Larastan, Laravel Pint |
| Docker | FrankenPHP + MySQL 8.4 |

Fonts are self-hosted through `@fontsource` and bundled by Vite: the app makes no external requests.

## Architecture

CraftDB is a Laravel monolith with Livewire for server-side reactivity and Alpine.js for client-side canvas state.

- **Actions** (`app/Actions/`) hold the business logic, one class per use case, grouped by feature (`Diagram/`, `Team/`, `Parse/`, `Local/`). Controllers and Livewire components stay thin and delegate to Actions.
- **Livewire components** (`app/Livewire/`, `resources/views/`) render the UI. `BaseComponent` catches domain exceptions such as `AuthorizationDiagramException`.
- **Observers** (`app/Observers/`) handle side effects, such as invalidating the diagram cache (see [DIAGRAM_CACHING.md](DIAGRAM_CACHING.md)).
- **Canvas** (`resources/js/canvas/`): `classes/InfiniteCanvas.js` handles zoom, pan and the grid; Alpine data and stores in `data/` handle tables, areas, notes and SVG lines.
- **MCP** (`app/Mcp/`): the `craftdb` server and its tools. `McpAccess::user()` resolves the local user, and ownership checks always apply.

### Local user (no login)

There is no login, sign-up or password. A single local user is created on boot (`craftdb:local-user`) and authenticated automatically by the `AuthenticateLocalUser` middleware.

### Workspaces

Every diagram belongs to a workspace (the `Team` model internally). See [WORKSPACES.md](WORKSPACES.md).

### Data flow

```
Browser ── Livewire component ── Action ── Model ── Database
   │
   └── Alpine.js + InfiniteCanvas (zoom, pan, drag, SVG lines)
```

## Project structure

```
app/
├── Actions/        # business logic (Diagram, Team, Parse, Local)
├── Enums/          # ColumnType, RelationshipType
├── Exceptions/     # domain exceptions
├── Http/           # thin controllers and middleware
├── Livewire/       # base components, forms, data objects
├── Mcp/            # MCP server and tools
├── Models/         # Diagram, Table, Column, Relationship, Area, Note, Team, User
├── Observers/      # side effects (cache invalidation)
└── Support/        # helpers
config/craftdb.php  # local user, HTTPS flag, support links
docker/             # entrypoint and smoke test
resources/
├── js/canvas/      # InfiniteCanvas + Alpine data/stores
└── views/          # Blade and Livewire views
routes/web.php      # all routes
tests/              # Pest (Feature and Unit)
```

## Running without Docker

Requirements: PHP 8.2+, Composer, Node.js. The development `.env` uses SQLite by default.

```bash
composer setup     # install dependencies, create .env, generate the key, migrate, build assets
composer run dev   # php artisan serve + queue + pail (logs) + Vite
```

## Tests

```bash
composer test            # or ./vendor/bin/pest
```

The suite runs on SQLite in memory. `tests/Feature/Telemetry/` guards the zero-telemetry rule.

## Docker smoke test

```bash
bash docker/smoke-test.sh
```

It builds the stack from scratch and checks boot, the local user, persistence, reset and the absence of external hosts in the pages. **Warning:** it runs `docker compose down -v`, which deletes the stack's data. Back up first (see the README).
