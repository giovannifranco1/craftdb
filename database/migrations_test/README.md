# migrations_test — fixture de importação

Migrations de exemplo usadas **só para testar a importação de migrations Laravel** do CraftDB
(`App\Actions\Parse\ParseLaravelMigration`) e gerar prints para a landing page.

Reproduz o schema do diagrama **"Blog Platform — Demo"**
(`https://craftdb.app/1/diagram/blog-platform-demo-E3dQTdfcLt`).

⚠️ **Esta pasta não é carregada pelo `php artisan migrate`.** O Laravel só varre
`database/migrations`. Nenhum path custom está registrado no projeto, então estes arquivos
nunca rodam contra o banco — são fixture de parser, não migrations reais.

## Como usar

Abra um diagrama → **Import** → selecione os 9 arquivos `.php` de uma vez.
O parser processa todos numa única transação, então a ordem de seleção não importa:
tabelas são criadas no primeiro passe e os relacionamentos no segundo, quando todas já existem.

## Resultado esperado

**9 tabelas · 58 colunas · 10 relacionamentos**

| Tabela | Colunas | FKs |
|---|---|---|
| `users` | 9 | — |
| `profiles` | 7 | `user_id` |
| `categories` | 5 | — |
| `tags` | 3 | — |
| `posts` | 13 | `user_id`, `category_id` |
| `post_tag` | 3 | `post_id`, `tag_id` |
| `media` | 7 | `post_id` |
| `comments` | 7 | `post_id`, `user_id` |
| `likes` | 4 | `user_id`, `post_id` |

Relacionamentos: `profiles.user_id→users.id`, `posts.user_id→users.id`,
`posts.category_id→categories.id`, `post_tag.post_id→posts.id`, `post_tag.tag_id→tags.id`,
`media.post_id→posts.id`, `comments.post_id→posts.id`, `comments.user_id→users.id`,
`likes.user_id→users.id`, `likes.post_id→posts.id`.

## Divergências esperadas em relação ao diagrama de origem

Vêm do `type_mapping` em `config/migration-parser.php` e **não são bug do fixture**:

- `posts.views_count` — `integer` no diagrama, importa como `int`.
- `comments.is_approved` — `boolean` no diagrama, importa como `tinyint`.
- `->unique()` não tem representação no modelo de dados do diagrama; é ignorado (sem coluna espúria).
- Áreas (`Usuários` / `Conteúdo` / `Interações`) não existem em migrations — o parser
  só cria tabelas, colunas e relacionamentos, posicionados em grid por `Helpers::calculateGridPosition()`.

## Restrições do parser que este fixture respeita de propósito

Se você editar estes arquivos, mantenha estas regras ou a importação quebra silenciosamente:

1. **Sem chaves aninhadas dentro do closure do `Schema::create`.** O regex
   `schema_patterns.create_table` é non-greedy (`{(.*?)}\s*\)`) e para no primeiro `}` seguido
   de `)`. Um `if {}` ou `match {}` no meio do blueprint trunca o bloco.
2. **Uma coluna por linha.** `parseColumns()` quebra o blueprint por `\n` **sem** normalizar
   chains. Quebrar `->nullable()` / `->default()` / `->comment()` em outra linha faz o
   modificador ser perdido (a coluna entra, mas sem o atributo).
3. **FK só é detectada** via `foreignId()->constrained()` ou
   `foreign()/references()/on()` — ver `foreign_key_patterns`.

## Gap conhecido do parser (não coberto aqui de propósito)

`$table->foreignIdFor(User::class)` **não gera coluna nem relacionamento**. Existe o pattern
`foreign_id_for` em `foreign_key_patterns`, mas `extractColumnData()` usa `column_pattern`,
que exige uma string entre quotes como primeiro argumento (`\(\s*['"]`). Como `User::class`
não é quoted, a coluna nunca é criada, e sem coluna o `createSingleRelationship()` também
descarta a FK. Ficou fora do fixture para o print sair correto — vale um bugfix separado.
