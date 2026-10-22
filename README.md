<p align="center">
  <img src="docs/images/logo.png" alt="CraftDB logo" width="96" height="96">
</p>

<h1 align="center">CraftDB</h1>

<p align="center">
  <strong>Design your database visually. 100% local, no account, no telemetry.</strong>
</p>

<p align="center">
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-AGPL--3.0-blue" alt="License: AGPL-3.0"></a>
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Docker-ready-2496ED?logo=docker&logoColor=white" alt="Docker ready">
</p>

<p align="center">
  🇺🇸 English · <a href="README.pt-BR.md">🇧🇷 Português</a>
</p>

![The CraftDB editor showing an e-commerce schema with customers, catalog and orders areas](docs/images/editor-light.png)

CraftDB is an open source database diagram editor. Draw tables, columns and relationships on an infinite canvas, organize them into areas, then export Laravel migrations or let your AI assistant build the schema for you through MCP. It runs on your machine with a single `docker compose up`.

## ✨ Why CraftDB

- 🏠 **100% local.** Runs on your computer with Docker. Your schemas live in your own MySQL database.
- 🔓 **No account, no login.** Open the browser and start drawing.
- 🙈 **Zero telemetry.** No analytics, no tracking, no calls to external services. Even the fonts are bundled.
- 💸 **Free and open source.** Every feature is available to everyone, licensed under AGPL-3.0.
- 🤖 **AI-ready.** A built-in MCP server lets Claude Code, Cursor and other assistants create and edit diagrams.

## 🧩 Features

### Visual editor

Create tables, add typed columns (primary keys, nullability, defaults, lengths) and connect them with **1:1, 1:n, n:1 and n:n** relationships. CraftDB creates the foreign key columns for you, and for n:n it creates the pivot table too. Zoom, pan and drag freely on an infinite canvas.

### Areas and notes

Group related tables into colored **areas** (like *Customers*, *Catalog* and *Orders* above) and leave **sticky notes** with conventions, decisions or TODOs right next to the schema.

### Dark mode

![The same diagram in dark mode](docs/images/editor-dark.png)

### Dashboard and workspaces

Every diagram belongs to a **workspace**. Use them to separate work, side projects or clients. Search, sort and favorite diagrams from the dashboard.

![Dashboard listing several diagrams](docs/images/dashboard.png)

![Workspaces settings page with three workspaces](docs/images/workspaces.png)

### Import

Start from what you already have:

- **SQL dump** (`.sql`, MySQL)
- **Laravel migrations** (a single `.php` file or a `.zip` with your `database/migrations` folder)

### Export

| Format | In the app | Through MCP |
|---|:---:|:---:|
| Laravel migrations (`.zip`, one file per table) | ✅ | ✅ |
| JSON | ✅ | ✅ |
| Mermaid ER diagram | | ✅ |

![Export modal with Laravel selected](docs/images/export-laravel.png)

This is the real `orders` migration generated from the diagram above:

```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users');
    $table->foreignId('address_id')->constrained('addresses');
    $table->foreignId('coupon_id')->nullable()->constrained('coupons');
    $table->string('status', 20)->default('pending');
    $table->decimal('total');
    $table->dateTime('placed_at')->nullable();
});
```

### MCP for AI assistants

CraftDB ships with an [MCP](https://modelcontextprotocol.io) server with 27 tools: create whole schemas in one call, add tables and columns, wire relationships, add areas and notes, move tables, and export. Once it is connected (see [Connect your AI assistant](#-connect-your-ai-assistant)), just ask:

> Using the craftdb MCP, create a diagram called "Helpdesk" in my Local Workspace with customers, agents, tickets, replies and tags. Group them into areas, add the relationships and leave a note explaining the ticket statuses.

The e-commerce diagram in the screenshots was created exactly this way.

## 🚀 Quick start

You only need [Docker](https://docs.docker.com/get-docker/) with Compose v2.

```bash
git clone https://github.com/giovannifranco1/craftdb.git
cd craftdb
docker compose up -d --build
```

Open **http://localhost:8080**. That's it: no sign-up, no login, you land straight on your dashboard.

The first build takes a few minutes. Later starts take seconds. To stop:

```bash
docker compose down        # stops the containers, keeps your data
```

> 🔒 Both the app and MySQL are bound to `127.0.0.1`, so nothing is exposed to your network.

## 🤖 Connect your AI assistant

The MCP server runs at `http://localhost:8080/mcp/craftdb`. It needs no token: it acts as your local user and only accepts local requests.

**Claude Code**

```bash
claude mcp add --transport http craftdb http://localhost:8080/mcp/craftdb
```

**Cursor**: add this to `.cursor/mcp.json` (or click *Add to Cursor* in the app):

```json
{
  "mcpServers": {
    "craftdb": {
      "url": "http://localhost:8080/mcp/craftdb"
    }
  }
}
```

**Any client that speaks stdio**

```bash
docker compose exec -T app php artisan mcp:start craftdb
```

Ready-to-copy snippets for Cursor, Claude and the terminal are also in **Settings → Integrations**.

![Settings → Integrations page with the local MCP URL and the Cursor setup](docs/images/mcp-integrations.png)

## ⚙️ Configuration

The defaults work out of the box. To change something, create a `.env` file next to `docker-compose.yml` (or set the variables in your shell) and run `docker compose up -d` again.

| Variable | Default | What it does |
|---|---|---|
| `CRAFTDB_PORT` | `8080` | Port of the web app (and of the MCP URL) |
| `CRAFTDB_DB_PORT` | `3307` | MySQL port on `127.0.0.1`, for your favorite SQL client |
| `CRAFTDB_DB_PASSWORD` | `craftdb` | MySQL password (applied on the first boot only) |
| `CRAFTDB_USER_EMAIL` | `local@craftdb.local` | E-mail of the local user (first boot only) |
| `CRAFTDB_APP_KEY` | generated on first boot | Application key. If empty, one is generated and kept in the `craftdb-data` volume |

Example:

```bash
# .env
CRAFTDB_PORT=8090
```

> The variables use the `CRAFTDB_` prefix on purpose: Docker Compose also reads this `.env`, which Laravel uses for local development.

### Support page links

The **Support** page (`/support`) shows only the links you fill in. All of them are optional and empty by default:

| Variable | Shows |
|---|---|
| `CRAFTDB_SUPPORT_REPO_URL` | Button to star the repository |
| `CRAFTDB_SUPPORT_GITHUB_SPONSORS_URL` | GitHub Sponsors button |
| `CRAFTDB_SUPPORT_KOFI_URL` | Ko-fi button |
| `CRAFTDB_SUPPORT_BUYMEACOFFEE_URL` | Buy Me a Coffee button |
| `CRAFTDB_SUPPORT_PIX_KEY` | Pix key with a copy button |

URLs must start with `http://` or `https://`. Add them to the same `.env` and run `docker compose up -d` to apply:

```bash
# .env
CRAFTDB_SUPPORT_REPO_URL=https://github.com/giovannifranco1/craftdb
CRAFTDB_SUPPORT_BUYMEACOFFEE_URL=https://buymeacoffee.com/your-page
```

You can connect to the database with any SQL client: host `127.0.0.1`, port `3307`, database `craftdb`, user `craftdb`, password `craftdb`.

## 🔄 Updating and backups

**Update to the latest version.** Database migrations run automatically on boot.

```bash
git pull
docker compose up -d --build
```

**Back up** your diagrams to a SQL file:

```bash
docker compose exec -T mysql sh -c 'exec mysqldump --no-tablespaces -u craftdb -p"$MYSQL_PASSWORD" craftdb' > craftdb-backup.sql
```

**Restore** a backup:

```bash
docker compose exec -T mysql sh -c 'exec mysql -u craftdb -p"$MYSQL_PASSWORD" craftdb' < craftdb-backup.sql
```

Your data lives in two Docker volumes, `craftdb-mysql` (database) and `craftdb-data` (app key). `docker compose down` keeps them. `docker compose down -v` **deletes them**, so take a backup first.

## 🗺️ Roadmap

Next up (M2, distribution): install and update without cloning the repo.

- 📦 Multi-arch Docker image on a public registry
- 🍺 A `craftdb` CLI via Homebrew (`start`, `stop`, `update`, `backup`)
- 💾 Automatic database backup before migrations

Ideas for later: a lite mode with SQLite, multi-user on a local network, and PostgreSQL/SQLite import parsers.

## 💜 Support

CraftDB is free and built in spare time. If it saves you time, you can:

- ⭐ Star the repo and share it with a friend
- 🐛 Report bugs and suggest features in the [issues](https://github.com/giovannifranco1/craftdb/issues)
- ☕ Buy me a coffee (link coming soon)
<!-- TODO: add the Buy Me a Coffee link here (and in README.pt-BR.md) once the page exists -->

The app also has a **Support** page at `/support`.

## 🤝 Contributing

Contributions are welcome! Open an [issue](https://github.com/giovannifranco1/craftdb/issues) to discuss bigger changes before sending a pull request.

To work on the code without Docker you need PHP 8.2+, Composer and Node.js:

```bash
composer setup     # install dependencies, create .env, migrate and build assets
composer run dev   # app server, queue, logs and Vite
composer test      # Pest test suite
```

Architecture, project structure and the Docker smoke test are described in [docs/DEVELOPMENT.md](docs/DEVELOPMENT.md).

## 📝 License

CraftDB is licensed under the [GNU Affero General Public License v3.0](LICENSE) (AGPL-3.0-or-later).

Made with ❤️ by **Giovanni Franco Rezende**.
