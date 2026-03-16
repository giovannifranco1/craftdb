# Convenções de Código

## Nomenclatura

**Arquivos PHP:**
- PascalCase para todas as classes
- Exemplos: `CreateNewDiagram.php`, `DiagramController.php`, `TableAccessible.php`, `BillingCountry.php`

**Arquivos Blade:**
- kebab-case para views e componentes
- Exemplos: `diagram-card.blade.php`, `infinite-canvas.blade.php`

**Arquivos JavaScript:**
- PascalCase para classes: `InfiniteCanvas.js`
- kebab-case para módulos/utilitários

**Funções/Métodos PHP:**
- camelCase
- Verbos descritivos: `makePublic()`, `revokePublic()`, `toggleFavorite()`, `hasReachedDiagramLimit()`
- Métodos de verificação de estado: `isPro()`, `isTeamPlan()`, `isEditor()`, `isViewer()`

**Variáveis:**
- camelCase em PHP
- snake_case em colunas de banco de dados
- Exemplos DB: `public_token`, `team_id`, `github_id`, `created_at`

**Rotas:**
- kebab-case nos segments de URL
- Nomeadas com ponto como separador: `diagram.show`, `billing.checkout`
- Pattern: `/{team_id}/diagram/{diagram:slug}`

**Enums:**
- PascalCase para casos: `Role::Editor`, `Role::Viewer`
- `BillingCountry::USD`, `BillingCountry::BRL`

## Organização de arquivos

**Estrutura interna de um Controller:**
```php
class BillingController extends Controller
{
    // Métodos públicos de ação diretos
    // Sem injeção de repositório — acessa modelo diretamente
    // Redireciona para Stripe ou retorna View

    public function checkout(Request $request, string $plan): RedirectResponse
    {
        // Lógica de negócio inline ou delegada para Cashier
    }

    public function portal(Request $request): RedirectResponse {}
    public function success(Request $request): RedirectResponse {}
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
    public function makePublic(): void {}
    public function makePrivate(): void {}
}
```

**Estrutura interna de uma Action:**
```php
class CreateNewDiagram
{
    public function handle(User $user, array $data): Diagram
    {
        // Validação de limites
        // Criação da entidade
        // Retorna entidade criada
    }
}
```

**Livewire Component:**
```php
class MyFavorites extends Component
{
    // Propriedades públicas reativas
    public $diagrams;

    // Hooks de lifecycle
    public function mount(): void {}

    // Métodos de ação chamados via wire:click
    public function toggleFavorite(int $id): void {}

    public function render(): View
    {
        return view('livewire.my-favorites');
    }
}
```

## Tipagem e documentação

**Abordagem:** PHP 8.2 native types — sem PHPDoc salvo onde necessário para generics/complex types
- Return types explícitos: `public function handle(): Diagram`
- Type hints em parâmetros: `public function checkout(Request $request, string $plan)`
- Nullable types com `?`: `?string $public_token`
- Enums para domínio fechado ao invés de constantes

## Tratamento de erros

**Padrão observado:** Exceções de domínio customizadas capturadas em `BaseComponent`
```php
// Action lança exceção de domínio
throw new TableLimitExceededException($limit);

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

**Global Scopes:** Em `app/Models/Scopes/` — ex: `TableAccessible` para filtrar por acesso de equipe
**Concerns (Traits):** Em `app/Models/Concerns/` — ex: `HasTeams` para helpers de relacionamento com equipes
