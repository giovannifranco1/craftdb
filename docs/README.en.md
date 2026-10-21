# CraftDB - Project Documentation

## 📋 Table of Contents

- [About the Project](#about-the-project)
- [Running with Docker](#running-with-docker)
- [Identity](#identity)
- [Technologies Used](#technologies-used)
- [Architecture](#architecture)
- [Project Structure](#project-structure)
- [Main Features](#main-features)
- [Support the project](#support-the-project)

---

## 🎯 About the Project

**CraftDB** is a modern web application for creating, visualizing, and managing database diagrams. The software allows development teams to create ER (Entity-Relationship) diagrams visually and interactively, facilitating the design and documentation of database structures.

### Features

- **Visual Diagram Creation**: Intuitive interface for creating database diagrams
- **Table Management**: Creation and editing of tables with their respective columns
- **Relationships**: Definition of relationships between tables (1:1, 1:N, N:N)
- **Graphical Visualization**: Interactive visualization of diagrams using force-directed graphs
- **Workspaces**: Group diagrams into local workspaces
- **Favorites**: System to bookmark favorite diagrams
- **Import**: Ability to import database structures
- **Export**: Ability to export diagrams in different formats

---

## 🏷️ Identity

**CraftDB** represents a professional tool for developers and software architects who need to:

- Document database structures
- Visualize relationships between entities
- Organize database diagrams into workspaces
- Maintain a clear view of their projects' data architecture

The name "CraftDB" reflects the idea of "craft" (craftsmanship/creation) combined with "DB" (Database), representing the careful and artisanal creation of database diagrams.

---

## 🛠️ Technologies Used

### Backend

- **PHP 8.2+**: Main programming language
- **Laravel 13**: Modern and robust PHP framework
- **Livewire 4**: Framework for creating reactive interfaces without writing JavaScript
- **Eloquent ORM**: Laravel's ORM for database interaction

### Frontend

- **Alpine.js 3.15**: Minimalist JavaScript framework for interactivity
- **D3.js 7.9**: Library for data visualization and graphs
- **Tailwind CSS 4**: Utility-first CSS framework
- **Livewire Flux 2**: Modern UI components for Livewire
- **Vite 7.0**: Modern and fast build tool

### Development Tools

- **Pest PHP 4.1**: Modern testing framework
- **Larastan 3.0**: Static code analysis (PHPStan)
- **Laravel Pint 1.18**: Code style fixer
- **Laravel Debugbar 3.16**: Debug bar for development
- **Laravel Pail 1.2**: Real-time log viewer

### Database

- **SQLite**: Default database for development
- **Eloquent Migrations**: Laravel's migration system

### Other Dependencies

- **Blade Lucide Icons 1.23**: Modern icons
- **Axios 1.7**: HTTP client for AJAX requests

---

## 🏗️ Architecture

**CraftDB** uses an **MVC (Model-View-Controller)** architecture combined with Livewire reactive components, following Laravel's patterns and conventions.

### Architectural Pattern

#### 1. **Model-View-Controller (MVC)**

- **Models** (`app/Models/`): Represent domain entities (Diagram, Table, Column, Relationship)
- **Views** (`resources/views/`): Blade templates for rendering
- **Controllers** (`app/Http/Controllers/`): Control HTTP request flow

#### 2. **Component-Based Architecture (Livewire)**

- **Livewire Components** (`app/Livewire/`): Reactive components that combine PHP logic and interface
- **Volt Components**: Single-file components (SFC) from Livewire 4
- Bidirectional communication between frontend and backend without REST APIs

#### 3. **Action Classes**

- **Actions** (`app/Actions/`): Classes that encapsulate specific business logic
  - `Diagram/`: Diagram-related actions
  - `Parse/`: Parsing and import actions
  - `Team/`: Workspace actions
  - `Local/`: Local user actions

#### 4. **Service Layer**

- **Services** (`app/Services/`): Utility services and helpers
  - `ButtonCvaService.php`: Button style management
  - `DialogCvaService.php`: Dialog management

#### 5. **Domain Models**

The project uses the following main models:

- **Diagram**: Represents a complete diagram
- **Table**: Represents a table in the diagram
- **Column**: Represents a column in a table
- **Relationship**: Represents a relationship between tables

### Data Flow

```
User → Livewire Component → Action/Service → Model → Database
            ↓
     View (Blade) ← Data ← Eloquent Query
            ↓
     JavaScript (Alpine.js/D3.js) → Interactivity
```

### Data Visualization

Diagram visualization uses **D3.js** with force algorithms (force-directed graph) to automatically position tables and relationships in a visually pleasing way:

- **Force Simulation**: Algorithm that calculates positions based on physical forces
- **Zoom and Pan**: Interactivity for navigation in large diagrams
- **Themes**: Support for light and dark modes

---

## 📁 Project Structure

```
craftdb/
├── app/
│   ├── Actions/          # Specific business logic
│   │   ├── Diagram/      # Diagram actions
│   │   ├── Local/        # Local user
│   │   ├── Team/         # Workspaces
│   │   └── Parse/        # Parsing actions
│   ├── Concerns/         # Reusable traits
│   ├── Enums/            # Enumerators (RelationshipType)
│   ├── Http/
│   │   └── Controllers/  # HTTP controllers
│   ├── Livewire/         # Livewire components
│   │   ├── Diagram/      # Diagram components
│   │   └── Forms/        # Forms
│   ├── Models/           # Eloquent models
│   ├── Policies/         # Authorization policies
│   ├── Providers/        # Service providers
│   ├── Rules/            # Custom validations
│   ├── Services/         # Utility services
│   └── View/
│       └── Components/   # Blade components
├── config/               # Configuration files
├── database/
│   ├── migrations/       # Database migrations
│   ├── factories/        # Test factories
│   └── seeders/          # Seeders
├── resources/
│   ├── css/              # CSS styles
│   ├── js/               # JavaScript (D3.js, Alpine.js)
│   └── views/            # Blade views
├── routes/               # Application routes
└── tests/                # Tests (Pest PHP)
```

---

## ⚡ Main Features

### 1. Diagram Management

- Creation of new diagrams
- Editing existing diagrams
- Deletion of diagrams
- Search and filtering of diagrams
- Favorites system

### 2. Table Management

- Creation of tables with positioning (x, y)
- Editing properties (name, color)
- Deletion of tables
- Table visualization on canvas

### 3. Column Management

- Adding columns to tables
- Data type definition
- Primary key marking
- Foreign key marking
- Column ordering

### 4. Relationships

- Creation of relationships between tables
- Relationship types:
  - **1:1** (One to One)
  - **1:N** (One to Many)
  - **N:1** (Many to One)
  - **N:N** (Many to Many)
- Graphical visualization of relationships

### 5. Graphical Visualization

- Interactive force graph (force-directed graph)
- Zoom and pan for navigation
- Theme support (light/dark)
- Foreign key visualization
- Real-time updates

### 6. Workspaces

- Create, rename and delete workspaces
- Switch between workspaces; every diagram belongs to a workspace
- No roles or invitations (see `docs/WORKSPACES.md`)

### 7. Local user (no login)

- A single implicit local user exists; there is no login, sign-up or password
- The app opens straight on the dashboard
- Your data stays in your own database (SQLite or MySQL)

## 💜 Support the project

CraftDB is free and open source. If it is useful to you, see the `/support` page in the app. The links it shows are set through environment variables (all optional; only filled ones appear):

- `CRAFTDB_SUPPORT_REPO_URL`
- `CRAFTDB_SUPPORT_GITHUB_SPONSORS_URL`
- `CRAFTDB_SUPPORT_KOFI_URL`
- `CRAFTDB_SUPPORT_BUYMEACOFFEE_URL`
- `CRAFTDB_SUPPORT_PIX_KEY`

---

## 🐳 Running with Docker

Requirement: Docker with Compose v2.

```bash
docker compose up -d          # starts app + MySQL (builds on the first run)
docker compose logs app       # shows the URL and the local user's credentials
docker compose down           # stops (data is kept)
docker compose down -v        # full reset (deletes database and key)
```

Open http://localhost:8080: the app opens straight on the dashboard, no login.

### Database

MySQL listens on `127.0.0.1:3307`: database `craftdb`, user `craftdb`, password `craftdb`. Any SQL client works (TablePlus, DBeaver).

### Variables

Set them in your shell before the first `up` (e.g. `CRAFTDB_PORT=8090 docker compose up -d`):

| Variable | Default | Purpose |
|---|---|---|
| `CRAFTDB_PORT` | `8080` | App port |
| `CRAFTDB_DB_PORT` | `3307` | MySQL port |
| `CRAFTDB_DB_PASSWORD` | `craftdb` | MySQL password (first boot only) |
| `CRAFTDB_USER_EMAIL` | `local@craftdb.local` | Local user's e-mail (first boot only) |
| `CRAFTDB_APP_KEY` | generated and stored in a volume | Application key |

Compose also reads the `.env` at the project root. That is the Laravel development `.env`, so the stack's variables use the `CRAFTDB_` prefix to avoid inheriting dev values.

If port 8080 or 3307 is taken, change it with `CRAFTDB_PORT` / `CRAFTDB_DB_PORT`.

Everything is bound to `127.0.0.1` only, so nothing is exposed on your network.

### MCP

The MCP server runs with no token and acts as the local user:

- HTTP: `http://localhost:8080/mcp/craftdb` (e.g. `.cursor/mcp.json` with `{"mcpServers":{"craftdb":{"url":"http://localhost:8080/mcp/craftdb"}}}`)
- stdio: `docker compose exec -T app php artisan mcp:start craftdb`

Ready-made snippets for Cursor, Claude Code and Claude Desktop are in Settings → Integrations (`/settings/integrations`).

### Smoke test

`bash docker/smoke-test.sh` boots the stack from scratch and checks boot, login, persistence, reset and absence of external hosts in pages (ZT-09). **Warning:** it runs `docker compose down -v`, which deletes the stack's data.

---

## 🚀 How to Run

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js and npm
- SQLite (or another supported database)

### Installation

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Build assets
npm run build
```

### Development

```bash
# Run development server
composer run dev
```

This command starts:
- Laravel server (PHP artisan serve)
- Queue worker
- Laravel Pail (logs)
- Vite dev server

### Testing

```bash
# Run tests
composer test
# or
php artisan test
```

---

## 📝 License

This project is licensed under the [GNU AGPL-3.0](../LICENSE) (AGPL-3.0-or-later).

---

## 👥 Contributing

Contributions are welcome! Please open an issue or pull request for discussion.

---

**CraftDB** - Creating database diagrams with art and precision. 🎨

