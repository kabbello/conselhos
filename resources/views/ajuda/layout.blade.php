<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Central de Ajuda') — Conselhos Municipais</title>
    <meta name="description" content="@yield('meta_description', 'Central de ajuda do sistema de Conselhos Municipais. Encontre guias, tutoriais e respostas para as dúvidas mais comuns.')">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

{{-- ── Header ──────────────────────────────────────────────────────────── --}}
<header class="bg-blue-700 text-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between gap-4">

        <div class="flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"/>
            </svg>
            <div>
                <p class="text-xs text-blue-300 leading-none">Conselhos Municipais</p>
                <h1 class="text-lg font-bold leading-tight">Central de Ajuda</h1>
            </div>
        </div>

        <nav class="flex items-center gap-4 text-sm">
            <a href="{{ url('/') }}" class="text-blue-200 hover:text-white transition-colors flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                </svg>
                Início
            </a>
            <a href="{{ route('filament.painel.auth.login') }}" class="text-blue-200 hover:text-white transition-colors flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/>
                </svg>
                Acessar o Painel
            </a>
        </nav>
    </div>
</header>

{{-- ── Busca ────────────────────────────────────────────────────────────── --}}
<div class="bg-blue-800 border-t border-blue-600">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="relative">
            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 15.803a7.5 7.5 0 0 0 10.607 0Z"/>
            </svg>
            <input
                type="search"
                id="ajuda-busca"
                placeholder="Buscar na ajuda..."
                autocomplete="off"
                class="w-full pl-10 pr-4 py-3 rounded-lg border border-slate-300 bg-white text-slate-800 shadow-sm focus:ring-2 focus:ring-blue-400 focus:outline-none text-base"
            >
        </div>
        <div id="ajuda-resultados" class="hidden mt-2 bg-white border border-slate-200 rounded-lg shadow-lg max-h-80 overflow-y-auto z-50 absolute left-4 right-4 sm:left-auto sm:right-auto sm:w-full" style="max-width: 768px;"></div>
    </div>
</div>

{{-- ── Conteúdo principal ────────────────────────────────────────────────── --}}
<main id="conteudo-principal" class="flex-1">
    @yield('content')
</main>

{{-- ── Footer ────────────────────────────────────────────────────────────── --}}
<footer class="bg-slate-800 text-slate-400 text-sm py-6 mt-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2">
        <p>Conselhos Municipais &mdash; Central de Ajuda</p>
        <a href="{{ route('ajuda.index') }}" class="hover:text-white transition-colors">Início da ajuda</a>
    </div>
</footer>

<script>
// Busca client-side na ajuda
(function () {
    const input = document.getElementById('ajuda-busca');
    const box   = document.getElementById('ajuda-resultados');
    if (!input || !box) return;

    // Índice de tópicos injetado pelo servidor
    const topicos = @json($topicosBusca ?? []);

    input.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        box.innerHTML = '';

        if (q.length < 2) {
            box.classList.add('hidden');
            return;
        }

        const matches = topicos.filter(t =>
            t.title.toLowerCase().includes(q) ||
            t.description.toLowerCase().includes(q) ||
            t.secao.toLowerCase().includes(q)
        ).slice(0, 10);

        if (!matches.length) {
            box.innerHTML = '<p class="px-4 py-3 text-slate-500 text-sm">Nenhum resultado encontrado.</p>';
            box.classList.remove('hidden');
            return;
        }

        matches.forEach(t => {
            const a = document.createElement('a');
            a.href = t.url;
            a.className = 'flex flex-col px-4 py-3 hover:bg-blue-50 border-b border-slate-100 last:border-0 transition-colors';
            a.innerHTML = `<span class="font-medium text-slate-800">${t.title}</span>
                           <span class="text-xs text-slate-500">${t.secaoLabel} &rsaquo; ${t.description}</span>`;
            box.appendChild(a);
        });

        box.classList.remove('hidden');
    });

    document.addEventListener('click', function (e) {
        if (!input.contains(e.target) && !box.contains(e.target)) {
            box.classList.add('hidden');
        }
    });
})();
</script>

@stack('scripts')
</body>
</html>
