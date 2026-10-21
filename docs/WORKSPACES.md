# Workspaces locais

No CraftDB Local existe um único usuário local implícito. Na interface, os agrupadores de diagramas são chamados de **Workspaces**. Internamente continuam com o nome `Team` (model `Team`, tabela `team`, coluna `team_id`); a renomeação interna é uma consideração futura.

Não há membros, convites nem papéis (roles): a tabela `team_user`, `Membership` e `HasMembers` foram removidos. A autorização é feita por **posse do workspace** (`$user->ownsTeam($team)`).

## Rotas

Definidas em `routes/web.php`:

- `/settings/workspaces` (`workspaces.index`) - página de gestão de workspaces

As rotas antigas `settings/team`, `settings/team/create` (`team.*`) e `/my-favorites` foram removidas.

## Funcionalidades da página

- Lista de workspaces com contagem de diagramas e marcador do workspace atual
- Criar workspace (o novo workspace passa a ser o atual)
- Renomear qualquer workspace
- Apagar workspace com confirmação digitando o nome (apaga todos os diagramas dele; recusa apagar o último workspace)
- Tornar um workspace o atual

## Componentes

- `resources/views/components/settings/⚡workspaces.blade.php` - página Livewire de gestão
- `resources/views/components/ui/⚡team-switcher/` - switcher sempre visível, com o nome do workspace atual e o link "Manage workspaces"
- `resources/views/components/settings/layout.blade.php` - navegação lateral

## Actions

- `App\Actions\Team\CreateNewTeam`
- `App\Actions\Team\RenameTeam`
- `App\Actions\Team\DeleteTeam` - executa `DeleteDiagram` para cada diagrama dentro de uma transação

## Modelos

- `App\Models\Team\Team` - workspace (`owner()`)
- `App\Models\Concerns\HasTeams` - `ownsTeam()`, `ownedTeams()`, `allTeams()` (workspaces do dono ordenados por nome), `firstTeam()`

## Workspace atual

O middleware `EnsureValidCurrentTeam` volta para `firstTeam()` quando o workspace atual deixa de existir, exibindo "The current workspace no longer exists. Switched to :name.". As contagens do menu e do dashboard são calculadas ao vivo (sem sessão/cache). Diagramas de qualquer workspace do usuário funcionam (notas, relacionamentos, links).

## Autorização

- `DiagramPolicy` e `TeamPolicy` - apenas o dono do workspace
- `TableAccessible` (scope), `Column::accessible` e `AddRelationship` - acesso por posse
- Gate `createDiagramOnTeam` (`AppServiceProvider`) delega para `DiagramPolicy::createOnTeam`

## MCP

O MCP mantém os nomes `list_teams` e `team_id` (ajuste previsto na F6).

## Testes

`tests/Feature/Workspaces/` (`OwnershipOnlyTest`, `WorkspacesPageTest`, `CrossWorkspaceTest`, `WorkspaceWordingTest`) e `tests/Feature/Actions/Team/` (`RenameTeamTest`, `DeleteTeamTest`). Helpers `secondTeam()` e `foreignTeam()` em `tests/TestCase.php`.
