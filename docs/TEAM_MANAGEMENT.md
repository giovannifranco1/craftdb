# Gerenciamento de Times - Team Management

No CraftDB Local existe um único usuário local implícito. Convites, membros e papéis (roles) foram removidos (F2): a autorização é feita por **posse do time** (`$user->ownsTeam($team)`).

## Rotas

Definidas em `routes/web.php`:

- `/settings/team` (`team.edit`) - informações do time (nome e proprietário)
- `/settings/team/create` (`team.create`) - criar novo time

## Componentes

- `resources/views/components/settings/⚡team.blade.php` - exibe e edita o nome do time e mostra o proprietário
- `resources/views/components/settings/team/⚡create.blade.php` - criação de time
- `resources/views/components/settings/layout.blade.php` - navegação lateral (Team Information e Create New Team)

## Autorização

- `DiagramPolicy::createOnTeam/update/share/delete` - apenas o dono do time
- `TeamPolicy::update/updateTeam/delete` - apenas o dono do time
- Gate `createDiagramOnTeam` (`AppServiceProvider`) delega para `DiagramPolicy::createOnTeam`

## Modelos

- `App\Models\Team\Team` - time (trait `HasMembers`: `owner()`, `users()`, `allUsers()`, `hasUser()`, `purge()`)
- `App\Models\Team\Membership` - pivot `team_user` (a coluna `role_id` ainda existe no banco; é removida na T7)
- `App\Models\Concerns\HasTeams` - `ownsTeam()`, `ownedTeams()`, `teams()`, `allTeams()`, `belongsToTeam()`

## Eventos Livewire

- `team-updated` - informações do time atualizadas
- `alert` - mensagens ao usuário
