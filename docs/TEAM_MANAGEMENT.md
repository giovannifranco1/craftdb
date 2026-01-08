# Gerenciamento de Equipes - Team Management

Este documento descreve as funcionalidades de gerenciamento de equipes implementadas no CraftDB.

## 📋 Visão Geral

O sistema de gerenciamento de equipes permite que usuários organizem e colaborem em times, com controle completo sobre membros, convites, funções e permissões.

## 🗺️ Estrutura de Rotas

As seguintes rotas foram implementadas em `routes/web.php`:

- **`/settings/team`** - Informações gerais do time
- **`/settings/team/members`** - Gerenciamento de membros
- **`/settings/team/invitations`** - Gerenciamento de convites
- **`/settings/team/roles`** - Gerenciamento de funções

## 📁 Arquivos Criados

### Views Livewire Volt

1. **`resources/views/livewire/settings/team.blade.php`**
   - Exibe e edita informações do time
   - Mostra proprietário do time
   - Permite alterar nome do time

2. **`resources/views/livewire/settings/team/members.blade.php`**
   - Lista todos os membros do time
   - Adicionar novos membros por email
   - Atribuir funções aos membros
   - Remover membros (exceto proprietário)
   - Alterar função de membros existentes

3. **`resources/views/livewire/settings/team/invitations.blade.php`**
   - Enviar convites por email
   - Listar convites pendentes
   - Reenviar convites
   - Cancelar convites

4. **`resources/views/livewire/settings/team/roles.blade.php`**
   - Criar novas funções
   - Editar funções existentes
   - Deletar funções (se não estiverem em uso)
   - Visualizar quantos membros têm cada função

### Componentes

5. **`resources/views/components/settings/team-layout.blade.php`**
   - Layout especializado para páginas de gerenciamento de time
   - Inclui navegação lateral com todas as opções de settings
   - Grupo expansível para "Team Settings"

### Layout Atualizado

6. **`resources/views/components/settings/layout.blade.php`**
   - Adicionada navegação para Team Settings
   - Grupo expansível com links para todas as telas de team

## 🌐 Traduções

Todas as strings foram traduzidas para português brasileiro em `lang/pt_BR.json`:

- Informações do Time
- Membros
- Convites
- Funções
- Mensagens de sucesso e erro
- Labels e placeholders

## 🎨 Design e UX

### Características

- **Interface Moderna**: Utiliza Flux UI components
- **Responsivo**: Funciona em desktop e mobile
- **Feedback Visual**: Mensagens de sucesso/erro via dispatch events
- **Confirmações**: Ações destrutivas requerem confirmação
- **Estados Vazios**: Mensagens informativas quando não há dados

### Padrões de UI

- **Cards com bordas**: Para itens de lista (membros, convites, funções)
- **Avatars**: Exibição de iniciais dos usuários
- **Badges**: Para indicar status (Owner, role names)
- **Formulários inline**: Adicionar/editar sem sair da página
- **Separadores visuais**: Divisão clara entre seções

## 🔐 Segurança e Validações

### Validações Implementadas

1. **Membros**:
   - Email deve existir no sistema
   - Usuário não pode já ser membro
   - Proprietário não pode ser removido
   - Proprietário não pode ter função alterada

2. **Convites**:
   - Email válido obrigatório
   - Não permitir convites duplicados
   - Verificar se usuário já é membro

3. **Funções**:
   - Nome e código obrigatórios
   - Código deve ser único por time
   - Código não pode ser alterado após criação
   - Não deletar funções em uso

## 📊 Modelos Relacionados

O sistema utiliza os seguintes modelos do pacote `jurager/teams`:

- **`Team`**: Representa um time
- **`Membership`**: Tabela pivot entre User e Team
- **`Invitation`**: Convites pendentes
- **`Role`**: Funções disponíveis no time
- **`Permission`**: Permissões associadas a funções

## 🚀 Funcionalidades

### Informações do Time

- Visualizar nome do time
- Editar nome do time
- Visualizar proprietário (não editável)

### Gerenciamento de Membros

- Adicionar membros pelo email
- Atribuir função ao adicionar
- Listar todos os membros com suas funções
- Alterar função de um membro
- Remover membros do time
- Proteção: proprietário não pode ser removido ou ter função alterada

### Sistema de Convites

- Enviar convites por email
- Selecionar função para o convidado
- Listar convites pendentes com data de envio
- Reenviar convites
- Cancelar convites pendentes
- Validações contra duplicatas

### Gerenciamento de Funções

- Criar funções customizadas
- Definir nome, código e descrição
- Editar nome e descrição (código é imutável)
- Visualizar quantos membros têm cada função
- Deletar funções não utilizadas
- Proteção contra deletar funções em uso

## 🔄 Eventos Livewire

Os componentes disparam eventos para comunicação:

- `team-updated`: Quando informações do time são atualizadas
- `member-added`: Quando um membro é adicionado
- `member-removed`: Quando um membro é removido
- `invitation-sent`: Quando um convite é enviado
- `invitation-cancelled`: Quando um convite é cancelado
- `role-created`: Quando uma função é criada
- `role-updated`: Quando uma função é atualizada
- `role-deleted`: Quando uma função é deletada
- `alert`: Para exibir mensagens ao usuário

## 📝 Próximos Passos (Opcional)

Funcionalidades que podem ser implementadas no futuro:

1. **Permissões Granulares**: Tela para gerenciar permissões específicas
2. **Email de Convite**: Implementar envio real de emails
3. **Aceitação de Convite**: Página pública para aceitar convites
4. **Histórico de Atividades**: Log de ações dos membros
5. **Transferência de Propriedade**: Permitir mudar o owner
6. **Múltiplos Times**: Permitir usuário pertencer a vários times
7. **Avatars Customizados**: Upload de imagem para times e usuários

## 🧪 Como Testar

1. Acesse `/settings/team` após fazer login
2. Navegue pelas diferentes seções no menu lateral
3. Experimente adicionar membros, enviar convites e criar funções
4. Verifique as validações tentando ações inválidas
5. Teste a responsividade em diferentes tamanhos de tela

## 🛠️ Tecnologias Utilizadas

- **Laravel 12**: Framework PHP
- **Livewire Volt**: Componentes reativos
- **Flux UI**: Biblioteca de componentes
- **jurager/teams**: Pacote de gerenciamento de times
- **Tailwind CSS**: Estilização

---

**Desenvolvido com ❤️ para CraftDB**
