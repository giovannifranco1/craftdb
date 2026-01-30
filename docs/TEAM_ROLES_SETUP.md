# Setup de Roles Simplificado

## 🎯 Mudanças Implementadas

O sistema de gerenciamento de equipes foi **simplificado** para trabalhar apenas com **2 roles fixas**:

- ✏️ **Editor** - Pode criar, editar e visualizar diagramas
- 👁️ **Visualizador** - Pode apenas visualizar diagramas

## ✅ O que foi feito

### 1. Auto-criação de Roles
- Quando um novo team é criado, as roles **Editor** e **Visualizador** são criadas automaticamente
- Não é mais necessário criar roles manualmente

### 2. Telas Simplificadas
- ✅ **Membros**: Interface simplificada com radio buttons mostrando descrição de cada role
- ✅ **Convites**: Mesma interface simplificada
- ❌ **Gerenciamento de Roles**: Tela removida (não é mais necessária)

### 3. Navegação Limpa
- Menu lateral atualizado sem o link "Roles"
- Apenas 3 seções: Team Information, Members, Invitations

## 🚀 Como executar o setup

### Para teams existentes que ainda não têm roles:

Execute o seeder para criar as roles padrão:

```bash
php artisan db:seed --class=DefaultRolesSeeder
```

Este comando irá:
- Verificar todos os teams existentes
- Criar as roles Editor e Visualizador para teams que não têm roles
- Pular teams que já possuem roles

### Para novos teams:

Não precisa fazer nada! As roles serão criadas automaticamente quando o team for criado.

## 📋 Estrutura das Roles

Cada team terá sempre estas 2 roles:

| Code | Nome | Descrição |
|------|------|-----------|
| `editor` | Editor | Pode criar, editar e visualizar diagramas |
| `viewer` | Visualizador | Pode apenas visualizar diagramas |

## 🎨 Interface do Usuário

### Ao adicionar um membro:

```
┌─────────────────────────────────────┐
│ ⚪ Editor                           │
│    Pode criar, editar e visualizar │
│    diagramas                        │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ ⚪ Visualizador                     │
│    Pode apenas visualizar diagramas │
└─────────────────────────────────────┘
```

### Na lista de membros:

- Dropdown simples com apenas 2 opções: Editor | Visualizador
- Owner não pode ter role alterada (mostra badge "Team Owner")

## 🔧 Arquivos Modificados

### Criados/Atualizados:
- `app/Models/Team/Team.php` - Adicionado evento `booted()` para auto-criação
- `database/seeders/DefaultRolesSeeder.php` - Seeder para teams existentes
- `resources/views/livewire/settings/team/members.blade.php` - Interface simplificada
- `resources/views/livewire/settings/team/invitations.blade.php` - Interface simplificada
- `resources/views/components/settings/layout.blade.php` - Menu atualizado
- `resources/views/components/settings/team-layout.blade.php` - Menu atualizado
- `lang/pt_BR.json` - Traduções adicionadas

### Removidos:
- `resources/views/livewire/settings/team/roles.blade.php` - Não é mais necessário
- Rota `settings/team/roles` removida de `routes/web.php`

## ✨ Benefícios

1. **Mais Simples**: Sem complexidade de criar/gerenciar roles
2. **Mais Rápido**: Setup automático
3. **Mais Claro**: Interface visual com descrições
4. **Menos Bugs**: Menos código = menos problemas
5. **Melhor UX**: Usuários sabem exatamente o que cada role faz

## 🎯 Próximos Passos (Opcional)

Se no futuro você quiser adicionar mais roles ou tornar o sistema flexível novamente:

1. Remover a auto-criação no modelo `Team`
2. Adicionar de volta a tela de gerenciamento
3. Tornar as roles dinâmicas

Mas para 99% dos casos, 2 roles fixas são suficientes! 🎉

---

**Implementado em:** {{ now()->format('d/m/Y') }}
