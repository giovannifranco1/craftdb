# CraftDB - Documentação do Projeto

## 📋 Índice

- [Sobre o Projeto](#sobre-o-projeto)
- [Rodando com Docker](#rodando-com-docker)
- [Identidade](#identidade)
- [Tecnologias Utilizadas](#tecnologias-utilizadas)
- [Arquitetura](#arquitetura)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Funcionalidades Principais](#funcionalidades-principais)

---

## 🎯 Sobre o Projeto

**CraftDB** é uma aplicação web moderna para criação, visualização e gerenciamento de diagramas de banco de dados. O software permite que equipes de desenvolvimento criem diagramas ER (Entidade-Relacionamento) de forma visual e interativa, facilitando o design e documentação de estruturas de banco de dados.

### Features

- **Criação Visual de Diagramas**: Interface intuitiva para criar diagramas de banco de dados
- **Gerenciamento de Tabelas**: Criação e edição de tabelas com suas respectivas colunas
- **Relacionamentos**: Definição de relacionamentos entre tabelas (1:1, 1:N, N:N)
- **Visualização Gráfica**: Visualização interativa dos diagramas usando gráficos de força (force-directed graphs)
- **Sistema de Equipes**: Colaboração em equipes com controle de acesso
- **Favoritos**: Sistema para marcar diagramas favoritos
- **Importação**: Capacidade de importar estruturas de banco de dados
- **Exportação**: Capacidade de exportar diagramas em diferentes formatos

---

## 🏷️ Identidade

**CraftDB** representa uma ferramenta profissional para desenvolvedores e arquitetos de software que precisam:

- Documentar estruturas de banco de dados
- Visualizar relacionamentos entre entidades
- Colaborar em equipes para projetar esquemas de banco de dados
- Manter uma visão clara da arquitetura de dados de seus projetos

O nome "CraftDB" reflete a ideia de "craft" (artesanato/criação) combinado com "DB" (Database), representando a criação cuidadosa e artesanal de diagramas de banco de dados.

---

## 🛠️ Tecnologias Utilizadas

### Backend

- **PHP 8.2+**: Linguagem de programação principal
- **Laravel 12**: Framework PHP moderno e robusto
- **Laravel Livewire 3.6**: Framework para criar interfaces reativas sem escrever JavaScript
- **Livewire Volt 1.7**: Abordagem funcional para componentes Livewire
- **Laravel Fortify 1.30**: Autenticação completa e segura
- **jurager/teams 3.0**: Sistema de equipes e permissões
- **Eloquent ORM**: ORM do Laravel para interação com banco de dados

### Frontend

- **Alpine.js 3.15**: Framework JavaScript minimalista para interatividade
- **D3.js 7.9**: Biblioteca para visualização de dados e gráficos
- **Tailwind CSS 4.0**: Framework CSS utility-first
- **Livewire Flux 2.1**: Componentes UI modernos para Livewire
- **Luvi UI 0.6**: Biblioteca de componentes UI adicionais
- **Vite 7.0**: Build tool moderna e rápida

### Ferramentas de Desenvolvimento

- **Pest PHP 4.1**: Framework de testes moderno
- **Larastan 3.0**: Análise estática de código (PHPStan)
- **Laravel Pint 1.18**: Code style fixer
- **Laravel Debugbar 3.16**: Barra de debug para desenvolvimento
- **Laravel Pail 1.2**: Visualizador de logs em tempo real

### Banco de Dados

- **SQLite**: Banco de dados padrão para desenvolvimento
- **Eloquent Migrations**: Sistema de migrações do Laravel

### Outras Dependências

- **Spatie Livewire Filepond 1.5**: Upload de arquivos
- **Blade Lucide Icons 1.23**: Ícones modernos
- **Axios 1.7**: Cliente HTTP para requisições AJAX

---

## 🏗️ Arquitetura

O **CraftDB** utiliza uma arquitetura **MVC (Model-View-Controller)** combinada com componentes reativos do Livewire, seguindo os padrões e convenções do Laravel.

### Padrão Arquitetural

#### 1. **Model-View-Controller (MVC)**

- **Models** (`app/Models/`): Representam as entidades do domínio (Diagram, Table, Column, Relationship)
- **Views** (`resources/views/`): Templates Blade para renderização
- **Controllers** (`app/Http/Controllers/`): Controlam o fluxo de requisições HTTP

#### 2. **Component-Based Architecture (Livewire)**

- **Livewire Components** (`app/Livewire/`): Componentes reativos que combinam lógica PHP e interface
- **Volt Components**: Componentes funcionais usando Livewire Volt
- Comunicação bidirecional entre frontend e backend sem necessidade de APIs REST

#### 3. **Action Classes**

- **Actions** (`app/Actions/`): Classes que encapsulam lógica de negócio específica
  - `Diagram/`: Ações relacionadas a diagramas
  - `Parse/`: Ações de parsing e importação
  - `Fortify/`: Ações de autenticação

#### 4. **Service Layer**

- **Services** (`app/Services/`): Serviços utilitários e helpers
  - `ButtonCvaService.php`: Gerenciamento de estilos de botões
  - `DialogCvaService.php`: Gerenciamento de diálogos

#### 5. **Domain Models**

O projeto utiliza os seguintes modelos principais:

- **Diagram**: Representa um diagrama completo
- **Table**: Representa uma tabela no diagrama
- **Column**: Representa uma coluna em uma tabela
- **Relationship**: Representa um relacionamento entre tabelas

### Fluxo de Dados

```
Usuário → Livewire Component → Action/Service → Model → Database
                ↓
         View (Blade) ← Dados ← Eloquent Query
                ↓
         JavaScript (Alpine.js/D3.js) → Interatividade
```

### Visualização de Dados

A visualização dos diagramas utiliza **D3.js** com algoritmos de força (force-directed graph) para posicionar automaticamente as tabelas e relacionamentos de forma visualmente agradável:

- **Force Simulation**: Algoritmo que calcula posições baseado em forças físicas
- **Zoom e Pan**: Interatividade para navegação em diagramas grandes
- **Temas**: Suporte a modo claro e escuro

---

## 📁 Estrutura do Projeto

```
craftdb/
├── app/
│   ├── Actions/          # Lógica de negócio específica
│   │   ├── Diagram/      # Ações de diagramas
│   │   ├── Fortify/      # Ações de autenticação
│   │   └── Parse/        # Ações de parsing
│   ├── Concerns/         # Traits reutilizáveis
│   ├── Enums/            # Enumeradores (RelationshipType)
│   ├── Http/
│   │   └── Controllers/  # Controladores HTTP
│   ├── Livewire/         # Componentes Livewire
│   │   ├── Diagram/      # Componentes de diagrama
│   │   └── Forms/        # Formulários
│   ├── Models/           # Modelos Eloquent
│   ├── Policies/         # Políticas de autorização
│   ├── Providers/        # Service Providers
│   ├── Rules/            # Validações customizadas
│   ├── Services/         # Serviços utilitários
│   └── View/
│       └── Components/   # Componentes Blade
├── config/               # Arquivos de configuração
├── database/
│   ├── migrations/       # Migrações do banco de dados
│   ├── factories/        # Factories para testes
│   └── seeders/          # Seeders
├── resources/
│   ├── css/              # Estilos CSS
│   ├── js/               # JavaScript (D3.js, Alpine.js)
│   └── views/            # Views Blade
├── routes/               # Rotas da aplicação
└── tests/                # Testes (Pest PHP)
```

---

## ⚡ Funcionalidades Principais

### 1. Gerenciamento de Diagramas

- Criação de novos diagramas
- Edição de diagramas existentes
- Exclusão de diagramas
- Busca e filtragem de diagramas
- Sistema de favoritos

### 2. Gerenciamento de Tabelas

- Criação de tabelas com posicionamento (x, y)
- Edição de propriedades (nome, cor)
- Exclusão de tabelas
- Visualização de tabelas no canvas

### 3. Gerenciamento de Colunas

- Adição de colunas às tabelas
- Definição de tipos de dados
- Marcação de chaves primárias
- Marcação de chaves estrangeiras
- Ordenação de colunas

### 4. Relacionamentos

- Criação de relacionamentos entre tabelas
- Tipos de relacionamento:
  - **1:1** (One to One)
  - **1:N** (One to Many)
  - **N:1** (Many to One)
  - **N:N** (Many to Many)
- Visualização gráfica dos relacionamentos

### 5. Visualização Gráfica

- Gráfico de força interativo (force-directed graph)
- Zoom e pan para navegação
- Suporte a temas (claro/escuro)
- Visualização de foreign keys
- Atualização em tempo real

### 6. Sistema de Equipes

- Criação e gerenciamento de equipes
- Controle de acesso baseado em roles
- Permissões granulares
- Convites para equipes

### 7. Autenticação e Segurança

- Autenticação completa com Laravel Fortify
- Autenticação de dois fatores (2FA)
- Recuperação de senha
- Verificação de email

---

## 🐳 Rodando com Docker

Requisito: Docker com Compose v2.

```bash
docker compose up -d          # sobe app + MySQL (a primeira vez faz o build)
docker compose logs app       # mostra a URL e as credenciais do usuário local
docker compose down           # para (os dados continuam)
docker compose down -v        # reset completo (apaga banco e chave)
```

Acesse http://localhost:8080: o app abre direto no dashboard, sem login.

### Banco de dados

O MySQL fica em `127.0.0.1:3307`: banco `craftdb`, usuário `craftdb`, senha `craftdb`. Dá para abrir em qualquer cliente SQL (TablePlus, DBeaver).

### Variáveis

Defina no shell antes do primeiro `up` (ex.: `CRAFTDB_PORT=8090 docker compose up -d`):

| Variável | Padrão | Para quê |
|---|---|---|
| `CRAFTDB_PORT` | `8080` | Porta do app |
| `CRAFTDB_DB_PORT` | `3307` | Porta do MySQL |
| `CRAFTDB_DB_PASSWORD` | `craftdb` | Senha do MySQL (só no primeiro boot) |
| `CRAFTDB_USER_EMAIL` | `local@craftdb.local` | E-mail do usuário local (só no primeiro boot) |
| `CRAFTDB_APP_KEY` | gerada e salva no volume | Chave da aplicação |

O Compose também lê o `.env` da raiz do projeto. Como esse é o `.env` do Laravel em desenvolvimento, as variáveis do stack usam o prefixo `CRAFTDB_` para não herdar valores de dev.

Se a porta 8080 ou 3307 já estiver em uso, troque com `CRAFTDB_PORT` / `CRAFTDB_DB_PORT`.

Tudo fica publicado só em `127.0.0.1`, então nada é exposto na sua rede.

### Smoke test

`bash docker/smoke-test.sh` sobe o stack do zero e verifica boot, login, persistência e reset. **Atenção:** ele roda `docker compose down -v` e apaga os dados do stack.

---

## 🚀 Como Executar

### Pré-requisitos

- PHP 8.2 ou superior
- Composer
- Node.js e npm
- SQLite (ou outro banco de dados suportado)

### Instalação

```bash
# Instalar dependências PHP
composer install

# Instalar dependências Node
npm install

# Configurar ambiente
cp .env.example .env
php artisan key:generate

# Executar migrações
php artisan migrate

# Compilar assets
npm run build
```

### Desenvolvimento

```bash
# Executar servidor de desenvolvimento
composer run dev
```

Este comando inicia:
- Servidor Laravel (PHP artisan serve)
- Queue worker
- Laravel Pail (logs)
- Vite dev server

### Testes

```bash
# Executar testes
composer test
# ou
php artisan test
```

---

## 🚀 Deploy em Produção

### Variáveis de Ambiente Obrigatórias

Além das variáveis padrão, configure no `.env` de produção:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com

# Banco de dados (MySQL ou PostgreSQL recomendados para produção)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=craftdb
DB_USERNAME=
DB_PASSWORD=

# Cache e filas (Redis recomendado)
CACHE_STORE=redis
QUEUE_CONNECTION=redis

# OAuth
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
```

### OAuth em Produção

Registre as URLs de callback nos provedores:

- **GitHub**: `https://seu-dominio.com/auth/callback`
- **Google**: `https://seu-dominio.com/auth/google/callback`

Certifique-se de que `APP_URL` esteja correto para os redirects.

### Filas e Scheduler

Configure um processo de fila (ex.: Supervisor) e o cron para o scheduler:

```bash
* * * * * cd /caminho/do/projeto && php artisan schedule:run >> /dev/null 2>&1
```

### Pré-lançamento

- [x] Middleware `ValidateLoginTermsAcceptance` registrado (valida aceite de termos no login)
- [ ] OAuth callbacks registrados

---

## 📝 Licença

Este projeto utiliza a licença MIT.

---

## 👥 Contribuindo

Contribuições são bem-vindas! Por favor, abra uma issue ou pull request para discussão.

---

**CraftDB** - Criando diagramas de banco de dados com arte e precisão. 🎨

