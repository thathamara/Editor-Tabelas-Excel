📌 Passo a Passo: Usando Vite no Laravel (Dev vs. Production)
1️⃣ Durante o Desenvolvimento
✅ Comandos:

powershell
# Inicie o servidor Vite (hot reload)
npm run dev

# Em outro terminal, inicie o Laravel
php artisan serve
🔹 O que acontece?

Vite monitora alterações em resources/js/ e resources/css/

Servidor Laravel roda em http://localhost:8000

2️⃣ Antes de Publicar (Build para Produção)
✅ Comandos:

powershell
# Gere os assets otimizados
npm run build

# Opcional: gere versão minificada
npm run production
🔹 O que acontece?

Arquivos são compilados para public/build/

Gera manifest.json para cache

3️⃣ No Seu Template Blade
html
<!-- No <head> -->
@vite(['resources/css/app.css', 'resources/js/app.js'])

<!-- Ou para produção -->
<link href="{{ asset('build/assets/app-123abc.css') }}" rel="stylesheet">
<script src="{{ asset('build/assets/app-xyz789.js') }}"></script>
⚠️ Lembre-se Sempre
Antes de commitar: Rode npm run build

No servidor: Instale dependências com npm install --production

Se mudar assets: Rebuild com npm run build

🔧 Solução Rápida para Problemas
powershell
# Se Vite não atualizar:
npm run dev -- --force

# Se houver cache:
npm cache clean --force && npm install