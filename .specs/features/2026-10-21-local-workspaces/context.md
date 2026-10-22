# Contexto — F4 Workspaces locais

**Origem:** ROADMAP M1/F4 · pivot total · regra: 1 commit por fase, sem push
**Data:** 2026-10-21
**Escopo:** Large · defaults (subagents + two-stage review)
**Aprovações:** dev ausente — decisões do **Lead** seguindo as opções recomendadas

## Decisões (Lead, dev ausente)

| # | Decisão | Alternativas descartadas |
|---|---|---|
| D1 | **Rename só na UI**: model `Team`, tabela `team`, coluna/param `team_id` ficam (rename interno segue em "Considerações Futuras"). Toda string visível vira **Workspace** (pt_BR: "Workspace"/"Workspaces" — termo de produto, sem traduzir) | Rename completo de model/tabela/rotas internas |
| D2 | **Uma tela `settings/workspaces`** (rota `workspaces.index`) lista os workspaces com ações **criar, renomear, apagar e tornar atual**. Substitui `settings/team` e `settings/team/create` (rotas `team.*` saem) | Manter telas separadas por time atual |
| D3 | **Apagar workspace apaga seus diagramas** (via `DeleteDiagram` em loop, numa Action `DeleteWorkspace` transacional), com confirmação digitando o nome. **Não é permitido apagar o último workspace** | Bloquear apagar workspace com diagramas; mover diagramas |
| D4 | **Sem membros**: migration apaga a tabela `team_user`; somem `Membership`, `users()`/`teams()`/`hasUser()`; `allTeams()` = workspaces próprios; `belongsToTeam` → `ownsTeam` (inclui `DiagramPolicy::view`, `TeamPolicy::view`, `TableAccessible`, `Column::accessible`) | Manter pivot vazio |
| D5 | **Workspace atual sempre visível**: o seletor aparece mesmo com 1 workspace, mostra o nome atual e leva a "Gerenciar workspaces" | Esconder com 1 |
| D6 | **Diagramas funcionam em qualquer workspace próprio**: remover as exigências "diagrama do time atual" (`⚡note` 403, `AddRelationship`, breadcrumb do `master` e link do `⚡create` usam o time do diagrama) | — |

## Decisões do agente

| # | Decisão | Motivo |
|---|---|---|
| A1 | Cache do contador do dashboard passa a incluir o id do workspace | Vazamento entre workspaces |
| A2 | `toggleFavorite` do dashboard ganha autorização (`update` do diagrama) | Mesma regra das outras ações |
| A3 | Rota `/my-favorites` + `App\Livewire\MyFavorites` (view inexistente) saem; favoritos seguem via `dashboard?favorites=true` | Rota quebrada (STATE) |
| A4 | `mcp_token_teams` não é tocado ao apagar workspace (ids mortos não dão acesso a nada); MCP/token é F6 | Evitar token restrito virar irrestrito |
| A5 | `EnsureValidCurrentTeam`: mensagem vira "O workspace atual não existe mais. Mudamos para {nome}." e fallback usa o mesmo critério do login (primeiro por id) | Texto falava de "acesso" (membros) |
| A6 | Strings mortas de membros/convites/papéis em `lang/pt_BR.json` saem | Limpeza da F2 que sobrou |
| A7 | MCP mantém nomes `list_teams`/`team_id` (contrato de tools) — textos já dizem "workspaces"; revisão na F6 | Não quebrar clientes MCP |
| A8 | Nome padrão do primeiro workspace continua "Local Workspace" (dado, não UI) | Sem migração de dados |

## Fatos (mapeamento 2026-10-21)

- `team`: só `user_id` (sem FK) e `name`; `session('current_team')` guarda o **model serializado**
- FKs da árvore do diagrama não têm cascade → apagar team com diagramas falha; `DeleteDiagram` já apaga a árvore na ordem certa
- `team_user.team_id` é `restrictOnDelete`; `RemoveAuthLeftoversTest:27` afirma que `team_user` existe (vai mudar)
- `Column::accessible()` só olha o pivot (bug latente para workspaces próprios)
- Sem testes para `EnsureValidCurrentTeam`, `SetCurrentTeamOnLogin`, `TeamPolicy`, tela de rename
