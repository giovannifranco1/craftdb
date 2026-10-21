# Convenções de Código

## Nomenclatura

**Arquivos PHP:**
- PascalCase para todas as classes
- Exemplos: `CreateNewDiagram.php`, `DiagramController.php`, `TableAccessible.php`

**Arquivos Blade:**
- kebab-case para views e componentes
- Exemplos: `diagram-card.blade.php`, `infinite-canvas.blade.php`

**Arquivos JavaScript:**
- PascalCase para classes: `InfiniteCanvas.js`
- kebab-case para módulos/utilitários

**Funções/Métodos PHP:**
- camelCase
- Verbos descritivos: `toggleFavorite()`, `exportJson()`
- Métodos de verificação de estado: `isFavorite()`, `ownsTeam()`

**Variáveis:**
- camelCase em PHP
- snake_case em colunas de banco de dados
- Exemplos DB: `team_id`, `diagram_id`, `created_at`
- Termo de UI: "Workspace"; `Team`/`team`/`team_id` é o nome interno (renomeação interna fica para o futuro). Textos visíveis (lang/*.json) usam "workspace", nunca "team/equipe/time"

**Rotas:**
- kebab-case nos segments de URL
- Nomeadas com ponto como separador: `diagram.show`, `diagram.export-json`
- Pattern: `/{team_id}/diagram/{diagram:slug}`

**Enums:**
- UPPER_SNAKE_CASE para casos: `ColumnType::VARCHAR`, `RelationshipType::ONE_TO_MANY`

## Organização de arquivos

**Estrutura interna de um Controller:**
```php
class DiagramController extends Controller
{
    // Métodos públicos de ação diretos
    // Sem injeção de repositório — acessa modelo diretamente
    // Delega regras de negócio para Actions; retorna View ou resposta JSON

    public function show(Request $request, string $team_id, Diagram $diagram): View {}
    public function toggleFavorite(Request $request, string $team_id, Diagram $diagram): JsonResponse {}
}
```

**Estrutura interna de um Model:**
```php
class Diagram extends Model
{
    use HasFactory;

    protected $fillable = [...];
    protected $casts = [...];

    // Relacionamentos
    public function tables(): HasMany {}
    public function team(): BelongsTo {}
    public function viewers(): BelongsToMany {}

    // Métodos de domínio
    public function isFavorite(): bool {}
}
```

**Estrutura interna de uma Action:**
```php
class CreateNewDiagram
{
    public function handle(User $user, array $data): Diagram
    {
        // Criação da entidade
        // Retorna entidade criada
    }
}
```

**Livewire Component:**
```php
class Workspaces extends Component
{
    // Propriedades públicas reativas
    public string $createName = '';

    // Hooks de lifecycle
    public function mount(): void {}

    // Métodos de ação chamados via wire:click
    public function startRename(int $id): void {}

    public function render(): View
    {
        return view('livewire.workspaces');
    }
}
```

## Tipagem e documentação

**Abordagem:** PHP 8.2 native types — sem PHPDoc salvo onde necessário para generics/complex types
- Return types explícitos: `public function handle(): Diagram`
- Type hints em parâmetros: `public function show(Request $request, string $team_id)`
- Nullable types com `?`: `?string $description`
- Enums para domínio fechado ao invés de constantes

## Tratamento de erros

**MCP:** tools obtêm o usuário via `McpAccess::user()` (nunca `auth()` direto) e sempre validam posse; violações lançam `AuthorizationException`.

**Padrão observado:** Exceções de domínio customizadas capturadas em `BaseComponent`
```php
// Action lança exceção de domínio
throw new AuthorizationDiagramException();

// BaseComponent captura e converte para feedback do usuário
protected function handleDomainException(DomainException $e): void
{
    $this->dispatch('notify', message: $e->getMessage(), type: 'error');
}
```
- Controllers usam try/catch inline ou deixam subir para handler global
- Sem uso de Result types

## Comentários

**Estilo:** Minimalista — código expressivo com pouco comentário
- PHPDoc apenas em métodos complexos ou com tipos genéricos
- Comentários inline para lógica não-óbvia (ex: detecção de país via CF header)
- Sem comentários de TODO/FIXME no código

## Relações entre Models

**Padrão:** Métodos de relacionamento Eloquent nomeados semanticamente
- `diagram->tables()` (não `diagram->diagramTables()`)
- `user->ownedDiagrams()`, `user->sharedDiagrams()`
- `table->columns()`, `column->table()`, `column->relationships()`

## Blade Components

**Nomenclatura:** Componentes anônimos em kebab-case, classes em PascalCase
- `<x-diagram-card>` → `resources/views/components/diagram/card.blade.php`
- `<x-layouts.app>` → `resources/views/components/layouts/app.blade.php`
- Flux UI via `<flux:*>` prefix

## Wireable DTOs

**Padrão:** Classes em `app/Livewire/Data/` que implementam `Livewire\Wireable` seguem três métodos obrigatórios:
```php
// Serializa para array (enviado ao frontend/request seguinte)
public function toLivewire(): array { ... }

// Reconstrói do array serializado (chamado pelo Livewire automaticamente)
public static function fromLivewire($value): self { ... }

// Cria a partir do model Eloquent
public static function fromModel(Model $model): self { ... }
```
**Quando usar:** Sempre que um DTO precisar ser propriedade pública de um Livewire Component — Livewire usa `toLivewire`/`fromLivewire` automaticamente ao serializar o estado.

## Alpine.js Canvas Stores

**Naming:** Data stores do canvas usam prefixo `run*` ao serem registrados (ex: `runTable`, `runCanvas`, `runArea`, `runNote`, `runSvgLines`)
**Arquivos:** kebab-case com prefixo `_` para indicar stores privados (ex: `_table.data.js`, `_canvas.data.js`)
**Hierarquia de z-index global:**
- Áreas (`areaZIndexCounter`): inicia em 500
- Notas (`noteZIndexCounter`): inicia em 600
- Tabelas (`tableZIndexCounter`): inicia em 1000
- Contadores são `window.*` para serem compartilhados entre múltiplos componentes Alpine

## Scopes e Concerns

**Global Scopes:** Em `app/Models/Scopes/` — ex: `TableAccessible` para filtrar por posse do workspace
**Concerns (Traits):** Em `app/Models/Concerns/` — ex: `HasTeams` para helpers de workspaces (`ownsTeam`, `ownedTeams`, `allTeams`, `firstTeam`)
