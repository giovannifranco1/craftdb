# Spec — Seções "Ferramenta em uso" na Landing Page

**Feature:** `SHOWCASE`
**Data:** 2026-07-24
**Escopo:** Medium (frontend / Blade + i18n)
**Status:** Draft — aguardando aprovação

## Contexto

A landing page (`resources/views/pages/landing-page/`) já usa um mockup de MacBook
no hero (`sections/hero.blade.php:50-59`): duas imagens em camada — `macbook.png`
(moldura) + `image_macbook.png` (print da tela), com sombra. O dev quer **novas
seções mostrando a ferramenta em uso**, reaproveitando esse mockup, deixando tudo
pronto e **faltando apenas trocar as imagens** pelos prints reais.

## Decisões (discovery)

- **3 seções showcase**, mapeando os pilares já anunciados na LP:
  1. Modelagem visual no canvas
  2. Importação SQL / Laravel Migration
  3. Colaboração em time + link público
- **Placeholders com nome final**: cada seção referencia um arquivo de imagem novo
  (`showcase-canvas.png`, `showcase-import.png`, `showcase-collab.png`) que nasce
  como cópia de uma imagem existente. Build fica verde; o dev só substitui o
  conteúdo do arquivo depois (nome/caminho não mudam).
- Seções **data-driven** (loop sobre array de tradução), seguindo o padrão de
  `features.blade.php` e `how-it-works.blade.php`.
- Mockup vira **componente Blade reutilizável** (DRY) e o hero passa a consumi-lo.

## Requisitos

### SHOWCASE-01 — Componente reutilizável do mockup
- **QUANDO** qualquer view usar `<x-landing-page.macbook-mockup :screen="..." :alt="..." />`
- **ENTÃO** renderiza a moldura `macbook.png` + a imagem de tela informada em `screen`,
  na mesma proporção/posição do hero (screen em `w-[70%] top-[14%] left-[15%]`), com a sombra.
- **E** aceita classes extras no elemento raiz (merge de atributos) para animações/posicionamento.
- **E** o `screen` é resolvido via `Vite::asset()`, consistente com o resto da LP.

### SHOWCASE-02 — Hero refatorado para o componente
- **QUANDO** a home renderiza o hero
- **ENTÃO** o mockup é o novo componente (não mais markup inline duplicado)
- **E** o resultado visual do hero permanece idêntico ao atual (mesma imagem, animação `fade-in-up fade-in-delay-3`, sombra).

### SHOWCASE-03 — Seção showcase data-driven
- **QUANDO** a home renderiza a nova seção `sections/showcase.blade.php`
- **ENTÃO** ela faz loop sobre `__('landing-page.showcase.items')` (3 itens)
- **E** cada item exibe: título, descrição, lista de destaques (bullets) e o mockup do MacBook com a imagem do item
- **E** o layout **alterna o lado** do mockup (par: texto/mockup; ímpar: mockup/texto) em `lg`, empilhando no mobile
- **E** usa as classes visuais da LP (`container`, `gradient-text`, `scroll-animate`, `fade-in-*`).

### SHOWCASE-04 — Traduções PT-BR e EN
- **QUANDO** o app roda em `pt_BR` ou `en`
- **ENTÃO** existe a chave `landing-page.showcase` (`title`, `subtitle`, `items[]` com `title`, `description`, `bullets[]`, `image`, `alt`) nos dois arquivos, com conteúdo equivalente.
- **E** nenhuma chave `__('landing-page.showcase.*')` renderiza como texto cru.

### SHOWCASE-05 — Registro na home + imagens placeholder
- **QUANDO** o usuário acessa `/`
- **ENTÃO** a seção showcase aparece no fluxo do `index.blade.php` (após `how-it-works`, antes de `pricing`)
- **E** existem os arquivos `resources/images/landing-page/showcase-{canvas,import,collab}.png` (placeholders), de modo que `vite build` conclui sem erro de manifest.

## Fora de escopo

- Prints reais das telas (o dev coloca depois, sobrescrevendo os placeholders).
- Alterar hero copy, pricing, faq ou qualquer seção existente além do refactor do mockup.
- Testes automatizados de UI (mudança é view estática; verificação = build + render).

## Critérios de aceite (resumo verificável)

- [ ] `<x-landing-page.macbook-mockup>` existe e é usado no hero e nas 3 seções showcase.
- [ ] Hero visualmente idêntico ao atual.
- [ ] 3 itens showcase com lados alternados, responsivo.
- [ ] Chaves `showcase` presentes em `pt_BR` e `en`, sem texto cru.
- [ ] 3 placeholders de imagem com os nomes finais; `npm run build` verde.
- [ ] Nenhuma regressão de build/lint.

## Arquivos afetados (previsão)

| Arquivo | Ação |
|---|---|
| `resources/views/components/landing-page/macbook-mockup.blade.php` | **novo** — componente |
| `resources/views/pages/landing-page/sections/hero.blade.php` | editar — usar componente |
| `resources/views/pages/landing-page/sections/showcase.blade.php` | **novo** — seção |
| `resources/views/pages/landing-page/index.blade.php` | editar — incluir seção |
| `lang/pt_BR/landing-page.php` | editar — chave `showcase` |
| `lang/en/landing-page.php` | editar — chave `showcase` |
| `resources/images/landing-page/showcase-{canvas,import,collab}.png` | **novo** — placeholders |
