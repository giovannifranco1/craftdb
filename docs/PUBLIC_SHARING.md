# 🌐 Public Diagram Sharing

Esta feature permite que diagramas sejam compartilhados publicamente através de um link único, permitindo que qualquer pessoa visualize o diagrama sem necessidade de autenticação.

## 📋 Funcionalidades

- ✅ Compartilhamento público via link único
- ✅ Visualização read-only para visitantes
- ✅ Token UUID único e seguro
- ✅ Revogação de acesso a qualquer momento
- ✅ Interface simplificada para visitantes
- ✅ Mantém rota privada funcionando normalmente

## 🔐 Segurança

- **Token UUID**: Cada diagrama público recebe um token UUID único de 36 caracteres
- **Read-only**: Visitantes públicos podem apenas visualizar, não editar
- **Revogável**: O acesso pode ser revogado a qualquer momento
- **Independente**: Não expõe informações do time ou membros

## 🎯 Como Usar

### Para Membros do Time (Criar/Revogar)

1. Acesse o diagrama que deseja compartilhar
2. Clique no botão **"Compartilhar"** no topo da página
3. No dropdown, clique em **"Compartilhar Publicamente"**
4. O link público será gerado e copiado automaticamente
5. Compartilhe o link com quem desejar

**Para revogar o acesso:**
- Clique novamente em "Compartilhar"
- Clique em **"Tornar Privado"**
- O link público será invalidado imediatamente

### Para Visitantes (Visualizar)

1. Acesse o link público compartilhado
2. Formato: `https://seu-dominio.com/share/{token}`
3. O diagrama será exibido em modo visualização
4. Navegue pelo diagrama usando zoom e pan
5. Não é possível fazer edições

## 📁 Arquivos Modificados/Criados

### Backend

- ✅ **Migration**: `database/migrations/2025_10_21_204314_create_diagram_table.php`
  - Coluna `public_token` (UUID, nullable, unique)

- ✅ **Model**: `app/Models/Diagram.php`
  - `isPublic()`: verifica se o diagrama é público
  - `makePublic()`: gera token e torna público
  - `makePrivate()`: revoga acesso público
  - `getPublicUrl()`: retorna URL pública

- ✅ **Controller**: `app/Http/Controllers/Pages/DiagramController.php`
  - `showPublic()`: exibe diagrama público
  - `makePublic()`: endpoint para tornar público
  - `revokePublic()`: endpoint para revogar

- ✅ **Policy**: `app/Policies/DiagramPolicy.php`
  - `share()`: autoriza owners e editores a compartilhar

- ✅ **Routes**: `routes/web.php`
  - `GET /share/{token}`: rota pública (sem auth)
  - `POST /{team_id}/diagram/{slug}/make-public`: tornar público
  - `DELETE /{team_id}/diagram/{slug}/revoke-public`: revogar

### Frontend

- ✅ **Livewire Component**: `app/Livewire/Diagram/ShareDiagram.php`
  - Componente para gerenciar compartilhamento

- ✅ **View**: `resources/views/livewire/diagram/share-diagram.blade.php`
  - Interface compacta para dropdown

- ✅ **Public View**: `resources/views/pages/diagram/public.blade.php`
  - Layout simplificado para visitantes

- ✅ **Topbar**: `resources/views/components/diagram/canvas/ui/topbar.blade.php`
  - Integração do componente de compartilhamento

## 🔄 Fluxo de Dados

```
1. Usuário clica "Compartilhar Publicamente"
   ↓
2. Sistema gera UUID único
   ↓
3. UUID salvo em `diagram.public_token`
   ↓
4. URL pública gerada: /share/{token}
   ↓
5. Link copiado para clipboard
   ↓
6. Visitante acessa URL pública
   ↓
7. Sistema busca diagrama pelo token
   ↓
8. Exibe diagrama em modo read-only
```

## 🚀 Deploy

### Rodar Migrations

```bash
php artisan migrate
```

### Limpar Cache (se necessário)

```bash
php artisan route:clear
php artisan view:clear
php artisan config:clear
```

## 📊 Banco de Dados

### Coluna Adicionada

```sql
ALTER TABLE diagram 
ADD COLUMN public_token UUID NULL,
ADD UNIQUE INDEX public_token_unique (public_token);
```

## 🧪 Testando

1. **Criar link público:**
   ```
   - Acesse um diagrama
   - Clique em "Compartilhar" → "Compartilhar Publicamente"
   - Verifique se o link foi gerado
   ```

2. **Acessar publicamente:**
   ```
   - Abra uma aba anônima/privada do navegador
   - Cole o link público
   - Verifique se o diagrama é exibido sem necessidade de login
   ```

3. **Revogar acesso:**
   ```
   - Clique em "Compartilhar" → "Tornar Privado"
   - Tente acessar o link público novamente
   - Deve retornar 404
   ```

## ⚠️ Notas Importantes

- A rota privada (`/{team_id}/diagram/{slug}`) continua funcionando normalmente
- Apenas owners e editors podem compartilhar publicamente
- Visitors (viewers) não podem criar links públicos
- O token é gerado apenas uma vez (reutilizado se já existir)
- Ao revogar, o token é deletado permanentemente

## 🔮 Melhorias Futuras

- [ ] Analytics de visualizações públicas
- [ ] Expiração automática de links (TTL)
- [ ] QR Code para compartilhamento
- [ ] Proteção por senha opcional
- [ ] Watermark customizável
- [ ] Export to PNG/PDF para visitantes públicos

## 📞 Suporte

Para dúvidas ou problemas, entre em contato com a equipe de desenvolvimento.
