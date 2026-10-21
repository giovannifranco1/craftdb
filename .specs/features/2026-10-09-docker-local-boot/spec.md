# F1 — Docker + MySQL + boot automático — Especificação

## Problema

Hoje o CraftDB só roda como SaaS ou num ambiente de dev com PHP, Node e Composer instalados. Para o pivot local, qualquer pessoa com Docker precisa subir o app e o banco com um comando, sem configurar nada, e conseguir entrar e acessar o banco pelo próprio cliente SQL.

## Objetivos

- [ ] De um clone limpo, `docker compose up -d` deixa o app acessível em `http://localhost:8080` sem nenhum passo manual
- [ ] Os dados (banco e chave da aplicação) sobrevivem a `docker compose down` + `up`
- [ ] O usuário consegue abrir o MySQL num cliente externo (TablePlus, DBeaver) em `127.0.0.1:3307`

## Fora do Escopo

| Item | Motivo |
|---|---|
| Remover login, Fortify, Socialite | F2 |
| Remover billing, limites de plano, links públicos | F3 |
| Remover Nightwatch, Google Fonts, Resend | F5 |
| Publicar imagem em registry, multi-arch, CLI `craftdb`, Homebrew | M2 (Distribuição) |
| Backup automático antes de migrations | M2 |
| Ambiente de desenvolvimento em Docker (hot reload, Vite dev server) | Fluxo de dev atual continua com `composer dev` |
| Queue worker e scheduler | Não há jobs; único agendamento é removido na F2 |
| HTTPS / domínio | Uso apenas em localhost |
| Suporte a SQLite ou Postgres no compose | Decisão D1: só MySQL |

---

## Glossário

| Termo | Definição canônica | Fonte |
|---|---|---|
| Usuário local | Usuário criado automaticamente no primeiro boot, com e-mail verificado e um workspace, usado para entrar no app até a F2 | decisão do dev (D3) |
| Workspace | Nome de produto para o model `Team` no modo local | discovery (seção 18 da nota) |
| Primeiro boot | Primeira subida do stack com volumes vazios | esta spec |
| Stack | Conjunto `app` + `mysql` definido no `docker-compose.yml` | esta spec |
| Super root | NÃO usar como sinônimo de usuário local — é o usuário administrativo do SaaS criado por migration | codebase |

---

## User Stories

### P1: Subir o CraftDB com um comando ⭐ MVP

**User Story**: Como dev com Docker instalado, quero subir o CraftDB com um comando para usar o app sem instalar PHP, Node ou MySQL.

**Por que P1**: É a base de todo o pivot local.

**Critérios de Aceite**:

1. QUANDO o usuário roda `docker compose up -d` num clone limpo, sem `.env` ENTÃO o sistema DEVE buildar a imagem do app, subir `app` e `mysql` e responder HTTP 200 em `http://localhost:8080/login` (DOCK-01)
2. QUANDO o `app` sobe ENTÃO ele DEVE aguardar o MySQL aceitar conexões antes de rodar qualquer comando de banco (DOCK-02)
3. QUANDO o `app` sobe ENTÃO ele DEVE rodar `migrate --force`, aplicando todas as migrations com sucesso no MySQL, e reiniciar sem erro quando não houver migrations pendentes (DOCK-03)
4. QUANDO a imagem é construída ENTÃO os assets do front (Vite) DEVEM estar compilados dentro dela, sem Node em runtime (DOCK-04)
5. QUANDO uma migration ou passo de boot falha ENTÃO o container `app` DEVE encerrar com código diferente de zero e a causa DEVE aparecer em `docker compose logs app` (DOCK-05)

**Teste Independente**: `git clone` em diretório limpo → `docker compose up -d` → `curl -o /dev/null -w '%{http_code}' http://localhost:8080/login` retorna 200.

---

### P1: Entrar com o usuário local ⭐ MVP

**User Story**: Como usuário local, quero entrar no app logo após subir o stack para criar diagramas sem cadastro nem verificação de e-mail.

**Por que P1**: Sem isso o app sobe mas não é utilizável (rotas exigem `auth` + `verified` e não há e-mail local).

**Critérios de Aceite**:

1. QUANDO ocorre o primeiro boot ENTÃO o sistema DEVE criar o usuário local com e-mail verificado e um workspace próprio (DOCK-06)
2. QUANDO ocorre o primeiro boot ENTÃO o log do `app` DEVE exibir o e-mail e a senha do usuário local e a URL de acesso (DOCK-07)
3. QUANDO o stack reinicia ENTÃO o sistema NÃO DEVE duplicar o usuário local nem alterar a senha dele (DOCK-08)
4. QUANDO o usuário faz login com essas credenciais ENTÃO o sistema DEVE abrir o dashboard e permitir criar um diagrama (DOCK-09)
5. QUANDO `CRAFTDB_USER_EMAIL` e/ou `CRAFTDB_USER_PASSWORD` estão definidos no ambiente no primeiro boot ENTÃO o usuário local DEVE ser criado com esses valores; senão, com e-mail `local@craftdb.local` e senha `craftdb` (DOCK-10)

**Teste Independente**: após o primeiro boot, `docker compose logs app` mostra as credenciais; login no navegador abre o dashboard; criar um diagrama funciona.

---

### P1: Dados persistentes ⭐ MVP

**User Story**: Como usuário local, quero que meus diagramas continuem lá depois de parar e subir o stack.

**Por que P1**: Sem persistência a ferramenta não serve para nada além de demo.

**Critérios de Aceite**:

1. QUANDO o usuário roda `docker compose down` e depois `docker compose up -d` ENTÃO diagramas, usuário e sessão DEVEM continuar existindo (DOCK-11)
2. QUANDO não há `APP_KEY` definida no ambiente no primeiro boot ENTÃO o sistema DEVE gerar uma e persisti-la em volume, reutilizando a mesma chave nos boots seguintes; QUANDO `APP_KEY` está definida no ambiente ENTÃO ela DEVE prevalecer sobre a chave do volume (DOCK-12)
3. QUANDO o usuário roda `docker compose down -v` ENTÃO o próximo `up` DEVE se comportar como primeiro boot (DOCK-13)

**Teste Independente**: criar diagrama → `down` → `up -d` → diagrama continua lá e a sessão do navegador continua válida.

---

### P1: Acesso local ao banco ⭐ MVP

**User Story**: Como dev, quero abrir o banco do CraftDB no meu cliente SQL para inspecionar ou exportar dados.

**Por que P1**: É um dos motivos do pivot (dados locais e acessíveis).

**Critérios de Aceite**:

1. QUANDO o stack está rodando ENTÃO o MySQL DEVE aceitar conexões em `127.0.0.1:3307` com as credenciais padrão documentadas (banco `craftdb`, usuário `craftdb`, senha `craftdb`), sobrescrevíveis por `CRAFTDB_DB_PASSWORD` antes do primeiro boot (DOCK-14)
2. QUANDO o stack está rodando ENTÃO nem o MySQL nem o app DEVEM estar publicados em interfaces além de `127.0.0.1` (DOCK-15)
3. QUANDO `CRAFTDB_PORT` ou `CRAFTDB_DB_PORT` estão definidos ENTÃO o app ou o MySQL DEVEM ser publicados nessas portas, e `APP_URL` (e a URL exibida no log) DEVE acompanhar `CRAFTDB_PORT` (DOCK-16)

**Teste Independente**: `mysql -h 127.0.0.1 -P 3307 -u craftdb -p` conecta e lista as tabelas; `docker compose ps` mostra as portas ligadas a `127.0.0.1`.

---

### P2: Documentação de uso

**User Story**: Como novo usuário, quero instruções curtas para subir, parar, resetar e acessar o banco.

**Por que P2**: O fluxo funciona sem docs, mas ninguém descobre credenciais, portas e reset sozinho.

**Critérios de Aceite**:

1. QUANDO o usuário abre o README ENTÃO DEVE encontrar uma seção "Rodando com Docker" com: subir, parar, ver logs/credenciais, resetar (`down -v`), acessar o MySQL e variáveis configuráveis (DOCK-17)

---

### P2: Healthcheck do app

**User Story**: Como usuário, quero que `docker compose ps` mostre se o app está pronto.

**Por que P2**: Ajuda a diagnosticar boot lento ou falho, mas não é essencial.

**Critérios de Aceite**:

1. QUANDO o app termina o boot e responde HTTP ENTÃO `docker compose ps` DEVE mostrar o serviço `app` como `healthy` (DOCK-18)

---

## Casos Limite

- QUANDO a porta 8080 ou 3307 já está em uso no host ENTÃO o `up` falha com o erro do Docker e o README DEVE indicar como trocar a porta (coberto por DOCK-16/DOCK-17)
- QUANDO o MySQL demora a inicializar no primeiro boot ENTÃO o app DEVE esperar em vez de falhar (DOCK-02)
- QUANDO o volume do banco já existe mas o volume da chave foi apagado ENTÃO o sistema DEVE gerar nova chave e continuar subindo; sessões antigas ficam inválidas (aceito)
- QUANDO o usuário roda `docker compose up -d` duas vezes seguidas ENTÃO nada é duplicado (DOCK-03, DOCK-08)
- QUANDO `CRAFTDB_USER_EMAIL` coincide com o e-mail de um usuário existente (ex.: super root) ENTÃO o sistema DEVE reaproveitar esse usuário, marcando o e-mail como verificado e garantindo o workspace, sem falhar por e-mail duplicado (DOCK-06)
- QUANDO o boot falha repetidamente ENTÃO o `app` NÃO DEVE reiniciar em loop infinito (política de restart limitada, definida no Design) (DOCK-05)

---

## Rastreabilidade de Requisitos

| ID | Story | Fase | Status |
|---|---|---|---|
| DOCK-01 | P1: Subir com um comando | Execute | Verified |
| DOCK-02 | P1: Subir com um comando | Execute | Verified |
| DOCK-03 | P1: Subir com um comando | Execute | Verified |
| DOCK-04 | P1: Subir com um comando | Execute | Verified |
| DOCK-05 | P1: Subir com um comando | Execute | Verified |
| DOCK-06 | P1: Usuário local | Execute | Verified |
| DOCK-07 | P1: Usuário local | Execute | Verified |
| DOCK-08 | P1: Usuário local | Execute | Verified |
| DOCK-09 | P1: Usuário local | Execute | Verified |
| DOCK-10 | P1: Usuário local | Execute | Verified |
| DOCK-11 | P1: Dados persistentes | Execute | Verified |
| DOCK-12 | P1: Dados persistentes | Execute | Verified |
| DOCK-13 | P1: Dados persistentes | Execute | Verified |
| DOCK-14 | P1: Acesso ao banco | Execute | Verified |
| DOCK-15 | P1: Acesso ao banco | Execute | Verified |
| DOCK-16 | P1: Acesso ao banco | Execute | Verified |
| DOCK-17 | P2: Documentação | Execute | Verified |
| DOCK-18 | P2: Healthcheck | Execute | Verified |

**Cobertura:** 18 total, 18 mapeados em tasks, 18 verificados (Pest + smoke test + checagens manuais DOCK-05/09/16)

---

## Critérios de Sucesso

- [ ] Clone limpo + `docker compose up -d` → login com usuário local → criar diagrama, sem nenhum outro comando
- [ ] Todas as migrations rodam em MySQL sem erro
- [ ] Dados persistem entre `down`/`up`
- [ ] Suite de testes atual (`composer test`, SQLite) continua verde; a validação em MySQL é feita via DOCK-03

---

## Notas Técnicas

- **Banco**: MySQL (versão LTS a confirmar no Design); migrations nunca validadas em MySQL, então podem surgir incompatibilidades
- **Super root**: as migrations já criam o usuário id 1 via `SUPER_ROOT_*` (senha padrão `changeme`), sem e-mail verificado. O usuário local é **separado** dele (e-mail padrão diferente); o super root continua existindo até a F2, acessível só em 127.0.0.1 (risco aceito)
- **Sessions/cache/queue**: driver `database` (já é o padrão), sem Redis
- **Credenciais do MySQL**: fixas e documentadas (DOCK-14), sobrescrevíveis por env; geração automática fica para o M2 (CLI)
- **Nightwatch**: compose e imagem não definem `NIGHTWATCH_TOKEN`; remoção completa na F5
- **Scopes afetados**: infra (Dockerfile, docker-compose.yml, entrypoint), config de banco, seed/comando de usuário local, README

---

## Self-Review

- [x] Placeholder scan — sem TBD/TODO/seções vazias
- [x] Consistência interna — sem contradições entre seções
- [x] Scope check — focada em um plano único (infra Docker + boot + usuário local + docs)
- [x] Ambiguity check — credenciais MySQL, APP_URL, precedência de APP_KEY, conflito de e-mail e restart esclarecidos após review
- [x] YAGNI — só features solicitadas pelo dev

**Reviewer:** `spec-document-reviewer` subagent (Large) — Approved, 0 issues, 6 recomendações aplicadas
**Status:** Aprovada pelo dev (2026-10-20)
**Data:** 2026-10-09
