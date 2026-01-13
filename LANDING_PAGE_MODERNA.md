# Landing Page Moderna - CraftDB

## 📋 Visão Geral

Nova landing page moderna criada com base no design React do projeto Loveable, convertida para Blade + Tailwind CSS.

## 🎨 Design

A landing page foi criada seguindo fielmente o design do projeto React original, com:

- **Estilo visual moderno** com gradientes e efeitos glass
- **Animações suaves** de scroll e hover
- **Responsividade completa** para mobile, tablet e desktop
- **Tema claro** com cores azul e ciano
- **Tipografia Outfit** (Google Fonts)

## 📁 Estrutura de Arquivos

```
resources/
├── css/
│   └── landing-page.css          # Estilos customizados e animações
├── views/
│   ├── components/
│   │   └── layouts/
│   │       └── landing-modern.blade.php  # Layout principal
│   └── landing-modern/
│       ├── index.blade.php       # Página principal
│       ├── partials/
│       │   ├── header.blade.php  # Header com menu mobile
│       │   └── footer.blade.php  # Footer com CTA e links
│       └── sections/
│           ├── hero.blade.php           # Hero com vídeo
│           ├── logo-strip.blade.php     # Logos de empresas
│           ├── features.blade.php       # Grid de recursos
│           ├── statistics.blade.php     # Estatísticas
│           ├── pricing.blade.php        # Planos e preços
│           ├── testimonials.blade.php   # Depoimentos
│           └── faq.blade.php            # Perguntas frequentes
```

## 🚀 Como Acessar

A nova landing page está disponível na rota:

```
/modern
```

Acesse: `http://localhost:8000/modern` (ou seu domínio)

## 🎯 Componentes Implementados

### ✅ Header
- Logo com badge "Beta"
- Menu de navegação desktop
- Menu mobile responsivo com animação
- CTAs de login e registro
- Sticky no topo com backdrop blur

### ✅ Hero
- Badge de prêmio e novidades
- Título com gradiente
- CTAs principais
- Mockup de laptop com player de vídeo
- Background com gradientes e efeitos blur

### ✅ Logo Strip
- Logos de empresas fictícias
- Efeito grayscale com hover
- Layout responsivo

### ✅ Features
- Grid 3 colunas (responsivo)
- 9 recursos principais
- Ícones SVG personalizados
- Cards com efeito glass e hover glow

### ✅ Statistics
- 4 estatísticas principais
- Ícones coloridos
- Layout responsivo 2x2

### ✅ Pricing
- 3 planos (Hobby, Pro, Team)
- Card destacado para plano popular
- Lista de recursos com checkmarks
- CTAs integrados com autenticação

### ✅ Testimonials
- 6 depoimentos
- Grid 3 colunas (responsivo)
- Avaliação com estrelas
- Avatares com gradiente
- Cards com hover effect

### ✅ FAQ
- 8 perguntas frequentes
- Accordion com Alpine.js
- Animações de abertura/fechamento
- Design clean e acessível

### ✅ Footer
- CTA final destacado
- 5 colunas de links
- Ícones sociais
- Status do sistema
- Copyright dinâmico

## 🎨 Estilos e Animações

### Classes CSS Customizadas

```css
.glass-card          # Card com efeito vidro
.gradient-text       # Texto com gradiente
.hero-gradient       # Background do hero
.section-gradient    # Background de seções
.glow-effect         # Efeito brilho no hover
.feature-icon        # Ícone de recurso
.btn-primary         # Botão primário
.btn-secondary       # Botão secundário
.btn-ghost           # Botão ghost
.nav-link            # Link de navegação
```

### Animações

```css
.fade-in             # Fade in simples
.fade-in-up          # Fade in com movimento
.fade-in-delay-1/2/3/4  # Delays escalonados
.scroll-animate      # Animação no scroll
.animate-float       # Flutuação suave
.animate-pulse-slow  # Pulse lento
```

## 🔧 Tecnologias Utilizadas

- **Laravel Blade** - Template engine
- **Tailwind CSS** - Framework CSS
- **Alpine.js** - JavaScript reativo
- **Google Fonts (Outfit)** - Tipografia
- **SVG Icons** - Ícones vetoriais

## 📱 Responsividade

A landing page é totalmente responsiva com breakpoints:

- **Mobile**: < 768px
- **Tablet**: 768px - 1024px
- **Desktop**: > 1024px

## 🎭 Funcionalidades Interativas

### Alpine.js
- Menu mobile toggle
- FAQ accordion
- Video player toggle
- Scroll animations

### Intersection Observer
- Animações ao entrar na viewport
- Performance otimizada
- Trigger único por elemento

## 🔗 Integração com Laravel

### Autenticação
Os componentes verificam automaticamente se o usuário está autenticado:

```blade
@auth
    <a href="{{ route('dashboard') }}">Dashboard</a>
@else
    <a href="{{ route('register') }}">Começar grátis</a>
@endauth
```

### Rotas
- `/modern` - Landing page moderna
- `/` - Landing page original (mantida)

## 🎨 Paleta de Cores

```css
Primary: #2563EB (Blue 600)
Secondary: #06B6D4 (Cyan 500)
Background: #FFFFFF
Text: #111827 (Gray 900)
Muted: #6B7280 (Gray 600)
Border: #E5E7EB (Gray 200)
```

## 📝 Próximos Passos (Opcional)

Se quiser adicionar os componentes que foram pulados:

1. **Deployment (Como funciona)** - Seção com passos do processo
2. **Collaboration (Scaling)** - Dashboard interativo com cursores animados

Esses componentes eram muito complexos e específicos do design original, mas podem ser adicionados posteriormente se necessário.

## 🐛 Troubleshooting

### Estilos não carregam
```bash
npm run build
# ou
npm run dev
```

### Alpine.js não funciona
Verifique se o script está carregando:
```blade
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
```

### Animações não aparecem
Verifique se o JavaScript de scroll está executando:
```javascript
document.querySelectorAll('.scroll-animate')
```

## 📄 Licença

Este projeto segue a mesma licença do CraftDB.

---

**Desenvolvido com ❤️ baseado no design React do Loveable**
