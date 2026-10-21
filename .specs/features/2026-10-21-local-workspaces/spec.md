# F4 — Workspaces locais — Especificação

## Problema

O usuário local organiza diagramas em "times", conceito herdado do SaaS colaborativo. Não há como apagar um time nem ver a lista deles; o seletor some com um só; diagramas de outro time dão 403 em notas e relacionamentos; o contador do dashboard vaza entre times; e restos do pivot de membros ainda são consultados.

## Objetivos

- [ ] O usuário gerencia **workspaces** (criar, renomear, apagar, alternar) numa tela única
- [ ] Nenhum texto visível fala em time/equipe/membros
- [ ] Diagramas funcionam em qualquer workspace do usuário
- [ ] Sem tabela/código de membros

## Fora do Escopo

| Item | Motivo |
|---|---|
| Renomear model `Team`, tabela `team`, `team_id` | Considerações Futuras (D1) |
| Nomes de tools MCP (`list_teams`, `team_id`), `mcp_token_teams` | F6 (A4, A7) |
| Telemetria, landing | F5, F7 |

---

## Glossário

| Termo | Definição canônica | Fonte |
|---|---|---|
| Workspace | Agrupamento de diagramas do usuário local (model `Team`) | D1 |
| Workspace atual | O workspace cujo conteúdo o dashboard mostra (sessão `current_team`) | código |
| Time / Equipe / Team (na UI) | NÃO usar na interface — usar Workspace | D1 |

---

## User Stories

### P1: Gerenciar workspaces ⭐ MVP

1. QUANDO o usuário abre `settings/workspaces` ENTÃO o sistema DEVE listar todos os seus workspaces com nome, quantidade de diagramas e indicação do atual (WS-01)
2. QUANDO o usuário cria um workspace com nome válido (1–255 caracteres) ENTÃO o sistema DEVE criá-lo, torná-lo o atual e mostrá-lo na lista (WS-02)
3. QUANDO o usuário renomeia um workspace (qualquer um, não só o atual) ENTÃO o sistema DEVE salvar o novo nome e, se for o atual, o seletor DEVE mostrar o novo nome em seguida (WS-03)
4. QUANDO o usuário apaga um workspace num modal que pede o nome (botão de confirmar desabilitado até o nome conferir) confirmando com o nome exato ENTÃO o sistema DEVE apagar o workspace e todos os seus diagramas (tabelas, colunas, relacionamentos, áreas, notas, visualizações) numa transação (WS-04)
5. QUANDO o usuário tenta apagar o único workspace ENTÃO o sistema DEVE recusar com mensagem clara (WS-05)
6. QUANDO o workspace apagado era o atual ENTÃO o sistema DEVE tornar atual outro workspace do usuário (WS-06)
7. QUANDO o usuário clica "tornar atual" ou escolhe no seletor ENTÃO o sistema DEVE trocar o workspace atual e abrir o dashboard dele (WS-07)
8. QUANDO a confirmação de apagar não confere com o nome ENTÃO o sistema NÃO DEVE apagar (WS-08)

**Teste Independente**: Pest Livewire na tela: criar, renomear, apagar com 2 diagramas (contagem de tabelas/colunas zera), recusar último, trocar atual.

---

### P1: Workspace sempre visível e textos coerentes ⭐ MVP

1. QUANDO qualquer tela do app é renderizada ENTÃO o seletor DEVE mostrar o nome do workspace atual (mesmo com 1 workspace) e, no dropdown, um item "Gerenciar workspaces" que leva a `workspaces.index` (WS-09)
2. QUANDO o app é renderizado (exceto landing) ENTÃO NÃO DEVE aparecer "Team", "Time", "Equipe", "Members", "Membros" ou "Convidar" em textos visíveis, incluindo mensagens/alertas vindos de PHP (Livewire, middleware, policies, exceções) (WS-10)
3. QUANDO a sessão aponta para um workspace que não existe mais ENTÃO o sistema DEVE trocar para o primeiro workspace do usuário e avisar "O workspace atual não existe mais…" (WS-11)
4. QUANDO o usuário abre o menu de configurações ENTÃO DEVE ver "Workspaces" (e não "Team Settings") (WS-12)
5. QUANDO as rotas são listadas ENTÃO `team.create`, `team.edit` e `dashboard.my-favorites` NÃO DEVEM existir e `workspaces.index` DEVE existir (WS-13)

---

### P1: Diagramas em qualquer workspace ⭐ MVP

1. QUANDO o usuário abre um diagrama de um workspace que não é o atual ENTÃO notas, relacionamentos e edição DEVEM funcionar sem 403 (WS-14)
2. QUANDO o breadcrumb e links de um diagrama são renderizados ENTÃO DEVEM usar o workspace do diagrama (WS-15)
3. QUANDO o usuário troca de workspace ENTÃO o contador do dashboard DEVE refletir o novo workspace imediatamente (WS-16)
4. QUANDO o usuário favorita um diagrama pelo dashboard ENTÃO o sistema DEVE autorizar como dono (WS-17)

---

### P1: Sem membros ⭐ MVP

1. QUANDO as migrations rodam ENTÃO a tabela `team_user` NÃO DEVE existir (WS-18)
2. QUANDO o código é inspecionado ENTÃO NÃO DEVEM existir `Membership`, `HasMembers::users/hasUser`, `HasTeams::teams`, `belongsToTeam`; acesso a workspace/diagrama/tabela/coluna é decidido por posse (`ownsTeam`) (WS-19)
3. QUANDO `Column::accessible()`/`TableAccessible` são usados ENTÃO DEVEM considerar os workspaces próprios do usuário (WS-20)
4. QUANDO a migration roda sobre um banco existente ENTÃO workspaces e diagramas DEVEM ser preservados (WS-21)

---

### P2: Testes e docs

1. QUANDO a suite roda ENTÃO testes de time viram testes de workspace e cobrem `EnsureValidCurrentTeam` e a tela de workspaces (WS-22)
2. QUANDO `composer test` e o smoke rodam ENTÃO DEVEM passar (WS-23)

---

## Casos Limite

- Nome vazio ou > 255 → erro de validação (WS-02/03)
- Dois workspaces com o mesmo nome → permitido (nome não é chave)
- Apagar workspace com diagrama aberto em outra aba → próxima ação nessa aba cai em 404/redirect (aceito)
- Token MCP restrito a um workspace apagado → continua sem acesso a ele (A4)

---

## Rastreabilidade

| ID | Story | Fase | Status |
|---|---|---|---|
| WS-01 | Gerenciar | Execute | Verified |
| WS-02 | Gerenciar | Execute | Verified |
| WS-03 | Gerenciar | Execute | Verified |
| WS-04 | Gerenciar | Execute | Verified |
| WS-05 | Gerenciar | Execute | Verified |
| WS-06 | Gerenciar | Execute | Verified |
| WS-07 | Gerenciar | Execute | Verified |
| WS-08 | Gerenciar | Execute | Verified |
| WS-09 | Visível/textos | Execute | Verified |
| WS-10 | Visível/textos | Execute | Verified |
| WS-11 | Visível/textos | Execute | Verified |
| WS-12 | Visível/textos | Execute | Verified |
| WS-13 | Visível/textos | Execute | Verified |
| WS-14 | Qualquer workspace | Execute | Verified |
| WS-15 | Qualquer workspace | Execute | Verified |
| WS-16 | Qualquer workspace | Execute | Verified |
| WS-17 | Qualquer workspace | Execute | Verified |
| WS-18 | Sem membros | Execute | Verified |
| WS-19 | Sem membros | Execute | Verified |
| WS-20 | Sem membros | Execute | Verified |
| WS-21 | Sem membros | Execute | Verified |
| WS-22 | Testes/docs | Execute | Verified |
| WS-23 | Testes/docs | Execute | Verified |

**Cobertura:** 23 total, 23 mapeados, 23 verificados (Pest + MySQL sobre volume existente + smoke)

---

## Critérios de Sucesso

- [ ] Criar, renomear, apagar (com diagramas) e alternar workspaces pela tela única
- [ ] `grep -rniE "\bteam(s)?\b|equipe|\btime\b|members|membros|convid" resources/views lang/pt_BR.json --include=*.blade.php --include=*.json | grep -v landing-page` sem textos visíveis (nomes de variável/rota/param internos são aceitos e listados no relatório)
- [ ] `composer test` e smoke verdes

**Reviewer:** spec-document-reviewer — Approved; recomendações aplicadas (sessão após rename, UX de confirmação, link no dropdown, mensagens PHP). MCP (`McpAccess`, `ListTeamsTool`, `mcp.authorize`) segue funcionando sem mudança porque `allTeams()` passa a ser só os próprios — verificado; `DiagramSeeder`/factories não usam o pivot.
**Status:** Aprovada pelo Lead (dev ausente)
**Data:** 2026-10-21
