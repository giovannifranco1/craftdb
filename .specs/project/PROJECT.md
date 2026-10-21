# CraftDB Local

**Visão:** Ferramenta de diagramas de banco de dados (ERD) que roda 100% local via Docker, sem login, sem telemetria e sem nada indo para a nuvem.
**Para:** Desenvolvedores (foco no ecossistema Laravel) e quem usa agentes de IA para modelar banco.
**Resolve:** Modelar e versionar schemas com privacidade total, sem depender de SaaS nem expor estrutura de dados de clientes.

## Objetivos

- Setup em 1 comando: do zero ao app aberto no navegador em menos de 5 minutos numa máquina com Docker
- Zero requisições de rede para fora da máquina em uso normal (verificável por auditoria de código e de tráfego)
- Adoção open source: estrelas, issues e contribuições como métrica principal na v1

## Stack Tecnológica

**Core:**

- Framework: Laravel 13 + Livewire 4 + Flux 2
- Linguagem: PHP 8.2+
- Banco de dados: MySQL em container dedicado (SQLite como possível modo lite futuro)

**Dependências-chave:** Livewire, Alpine.js + InfiniteCanvas, D3.js, phpmyadmin/sql-parser, Laravel MCP

## Escopo

**v1 inclui:**

- Distribuição via Docker Compose (app + MySQL) com boot automático
- Uso sem login: usuário local implícito
- Workspaces locais (reaproveitando Team, sem membros/convites/papéis)
- Todas as features liberadas (sem planos/limites)
- MCP local sem Passport
- Página "Apoiar" (GitHub Sponsors, Buy Me a Coffee, Pix, estrela)
- Remoção de telemetria e assets externos (Nightwatch, Resend, Google Fonts)

**Explicitamente fora de escopo:**

- Billing, planos, licença paga (plano B documentado na nota de discovery)
- Links públicos de compartilhamento
- Multiusuário em rede (LAN) — avaliar depois
- Conectar no banco do usuário para introspecção de schema
- Landing page / marketing dentro do app

## Restrições

- Técnicas: nenhuma chamada de rede de saída; porta do banco publicada só em 127.0.0.1
- Licença do código: open source (AGPL-3.0 sugerida, a confirmar)
- Recursos: mantido por um dev; mudanças incrementais por fase
