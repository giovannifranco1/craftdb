# Roadmap

**Milestone atual:** M1 — CraftDB Local v1
**Status:** Planejamento

---

## M1 — CraftDB Local v1

**Objetivo:** Qualquer dev sobe o CraftDB local com 1 comando, sem login, sem telemetria, com todas as features.
**Alvo:** Todas as fases abaixo concluídas e verificadas.

### Features

**F1 — Docker + MySQL + boot automático** - CONCLUÍDA (2026-10-21)

- Dockerfile de produção do app (assets buildados)
- docker-compose com app + MySQL
- Entrypoint: APP_KEY persistida, migrate, seed

**F2 — Usuário local implícito + remoção de auth** - PLANEJADA

- Seed de usuário/workspace local e auto-login por middleware
- Remove Fortify (login, registro, 2FA, reset), Socialite, convites, papéis

**F3 — Remoção de billing, limites e links públicos** - PLANEJADA

- Remove Cashier/Stripe, planos, DiagramLimits, pro-features, cf-ipcountry
- Remove rota e componentes de diagrama público

**F4 — Workspaces locais** - PLANEJADA

- Team vira Workspace na UI (criar, renomear, apagar, alternar)

**F5 — Zero telemetria** - PLANEJADA

- Remove Nightwatch, Resend, S3; self-host de fontes; remove og/URLs craftdb.app

**F6 — MCP local sem Passport** - PLANEJADA

- stdio sem token; HTTP só em localhost

**F7 — Remoção da landing + página Apoiar** - PLANEJADA

---

## M2 — Distribuição

**Objetivo:** Instalar e atualizar sem clonar o repo.

### Features

**Imagem multi-arch no GHCR/Docker Hub** - PLANEJADA
**CLI `craftdb` via Homebrew tap (start/stop/update/backup)** - PLANEJADA
**Backup automático do banco antes de migrations** - PLANEJADA

---

## Considerações Futuras

- Modo lite com SQLite
- Multiusuário em LAN (login opcional)
- Parsers PostgreSQL e SQLite (hoje vazios)
- Licença de apoiador opcional
- Rename interno Team → Workspace
