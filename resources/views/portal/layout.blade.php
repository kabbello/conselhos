<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $municipio->nome . ' — Portal dos Conselhos Municipais')</title>
    <meta name="description" content="@yield('meta_description', 'Portal público dos Conselhos Municipais de ' . $municipio->nome . '. Acesse atas, documentos, legislação e reuniões dos conselhos municipais.')">
    <meta property="og:title" content="@yield('title', $municipio->nome . ' — Portal dos Conselhos Municipais')">
    <meta property="og:description" content="@yield('meta_description', 'Portal público dos Conselhos Municipais de ' . $municipio->nome)">
    <meta property="og:type" content="website">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet">

    {{-- Aplica preferências de acessibilidade antes do render para evitar flash --}}
    <script>
        (function () {
            try {
                var contrast = localStorage.getItem('a11y-contrast');
                var fontSize = localStorage.getItem('a11y-fontsize');
                if (contrast === '1') document.documentElement.classList.add('alto-contraste');
                if (fontSize) document.documentElement.style.fontSize = fontSize + 'px';
            } catch (e) {}
        })();
    </script>

    <style>
        /* ── Acessibilidade: Alto Contraste ────────────────────────── */
        html.alto-contraste body          { background:#000 !important; color:#ff0 !important; }
        html.alto-contraste a             { color:#ff0 !important; text-decoration:underline !important; }
        html.alto-contraste header,
        html.alto-contraste footer        { background:#000 !important; border-color:#ff0 !important; }
        html.alto-contraste .bg-blue-700,
        html.alto-contraste .bg-blue-600,
        html.alto-contraste .from-blue-700,
        html.alto-contraste .to-blue-600  { background:#000 !important; border:2px solid #ff0 !important; }
        html.alto-contraste img           { filter:grayscale(100%) contrast(120%) !important; }
        html.alto-contraste *             { border-color:#ff0 !important; }
        html.alto-contraste .text-white,
        html.alto-contraste .text-blue-200,
        html.alto-contraste .text-blue-300,
        html.alto-contraste .text-slate-400,
        html.alto-contraste .text-slate-500,
        html.alto-contraste .text-slate-600 { color:#ff0 !important; }
        html.alto-contraste .bg-white,
        html.alto-contraste .bg-slate-50,
        html.alto-contraste .bg-slate-100  { background:#000 !important; color:#ff0 !important; }

        /* ── Skip to content ───────────────────────────────────────── */
        #pular-conteudo {
            position:absolute; left:-9999px; top:auto; width:1px; height:1px; overflow:hidden;
        }
        #pular-conteudo:focus {
            position:fixed; left:50%; top:0; transform:translateX(-50%);
            width:auto; height:auto; padding:.75rem 1.5rem;
            background:#1d4ed8; color:#fff; font-weight:700; font-size:1rem;
            border-radius:0 0 .5rem .5rem; z-index:9999; outline:3px solid #fbbf24;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

{{-- ── Skip to content (acessibilidade) ──────────────────────────────── --}}
<a id="pular-conteudo" href="#conteudo-principal">Pular para o conteúdo principal</a>

{{-- ── Barra de acessibilidade ────────────────────────────────────────── --}}
<div class="bg-slate-900 text-slate-300 text-xs" role="toolbar" aria-label="Ferramentas de acessibilidade">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-1.5 flex items-center justify-between gap-4 flex-wrap">

        <span class="text-slate-500 hidden sm:inline">Ferramentas de acessibilidade</span>

        <div class="flex items-center gap-1 ml-auto">
            {{-- Tamanho de fonte --}}
            <span class="text-slate-500 mr-1 hidden sm:inline">Fonte:</span>
            <button onclick="a11yFontSize(-2)"
                    title="Diminuir tamanho da fonte"
                    aria-label="Diminuir tamanho da fonte"
                    class="px-2 py-1 rounded hover:bg-slate-700 hover:text-white transition-colors font-bold text-sm leading-none">
                A-
            </button>
            <button onclick="a11yFontSize(0)"
                    title="Tamanho de fonte padrão"
                    aria-label="Tamanho de fonte padrão"
                    class="px-2 py-1 rounded hover:bg-slate-700 hover:text-white transition-colors font-medium leading-none">
                A
            </button>
            <button onclick="a11yFontSize(2)"
                    title="Aumentar tamanho da fonte"
                    aria-label="Aumentar tamanho da fonte"
                    class="px-2 py-1 rounded hover:bg-slate-700 hover:text-white transition-colors font-bold text-base leading-none">
                A+
            </button>

            <span class="w-px h-4 bg-slate-700 mx-1"></span>

            {{-- Alto contraste --}}
            <button id="btn-contraste"
                    onclick="a11yContraste()"
                    title="Alternar alto contraste"
                    aria-label="Alternar modo de alto contraste"
                    aria-pressed="false"
                    class="flex items-center gap-1.5 px-2 py-1 rounded hover:bg-slate-700 hover:text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"/><path d="M12 3v18" stroke-linecap="round"/>
                    <path d="M12 3a9 9 0 0 1 0 18" fill="currentColor"/>
                </svg>
                <span class="hidden sm:inline">Contraste</span>
            </button>

            <span class="w-px h-4 bg-slate-700 mx-1"></span>

            {{-- Reportar erro --}}
            <a href="mailto:{{ $municipio->email ?? 'contato@' . ($municipio->slug ?? 'municipio') . '.sp.gov.br' }}?subject=Erro%20no%20Portal%20dos%20Conselhos&body=Página%3A%20{{ urlencode(request()->fullUrl()) }}%0A%0ADescrição%20do%20erro%3A%20"
               title="Reportar um erro nesta página"
               aria-label="Reportar erro nesta página por e-mail"
               class="flex items-center gap-1.5 px-2 py-1 rounded hover:bg-slate-700 hover:text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
                <span class="hidden sm:inline">Reportar erro</span>
            </a>
        </div>
    </div>
</div>

{{-- ── Header principal ────────────────────────────────────────────────── --}}
<header class="bg-blue-700 text-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Top bar --}}
        <div class="flex items-center justify-between py-4">

            {{-- Logo + nome do município --}}
            <div class="flex items-center gap-4">
                {{-- Logo linkado para o portal --}}
                <a href="{{ route('portal.index', $municipio->slug) }}"
                   class="flex items-center gap-4 hover:opacity-90 transition-opacity"
                   aria-label="Página inicial do portal de {{ $municipio->nome }}">
                    @if($municipio->logo_path)
                        <img src="{{ Storage::disk('r2')->url($municipio->logo_path) }}"
                             alt="Logotipo de {{ $municipio->nome }}"
                             class="h-14 w-auto object-contain drop-shadow">
                    @else
                        <div class="h-14 w-14 rounded-full bg-white/20 flex items-center justify-center text-xl font-bold select-none" aria-hidden="true">
                            {{ strtoupper(substr($municipio->nome, 0, 2)) }}
                        </div>
                    @endif

                    <div>
                        <div class="text-xs font-medium uppercase tracking-widest text-blue-200">
                            Prefeitura Municipal de
                        </div>
                        <div class="text-2xl font-bold leading-tight">
                            {{ $municipio->nome }}
                            @if($municipio->uf)
                                <span class="text-blue-300 text-lg font-medium">/ {{ $municipio->uf }}</span>
                            @endif
                        </div>
                        <div class="text-sm text-blue-200 font-medium mt-0.5">
                            Portal dos Conselhos Municipais
                        </div>
                    </div>
                </a>

            </div>

            {{-- Navegação --}}
            <nav class="hidden md:flex items-center gap-1 text-sm font-medium" aria-label="Menu principal">
                <a href="{{ route('portal.index', $municipio->slug) }}"
                   class="px-4 py-2 rounded-lg hover:bg-white/15 transition-colors @yield('nav_conselhos')"
                   @yield('nav_conselhos_aria')>
                    Conselhos
                </a>
            </nav>
        </div>

        {{-- Breadcrumb --}}
        @hasSection('breadcrumb')
        <div class="border-t border-blue-600/50 py-2">
            <nav class="flex items-center gap-2 text-sm text-blue-200" aria-label="Localização na página">
                <a href="{{ route('portal.index', $municipio->slug) }}" class="hover:text-white transition-colors">
                    Conselhos
                </a>
                @yield('breadcrumb')
            </nav>
        </div>
        @endif
    </div>
</header>

{{-- ── Conteúdo principal ──────────────────────────────────────────────── --}}
<main id="conteudo-principal" class="flex-1" tabindex="-1">
    @yield('content')
</main>

{{-- ── Footer ──────────────────────────────────────────────────────────── --}}
<footer class="bg-slate-800 text-slate-400 mt-16" role="contentinfo">

    {{-- Bloco principal do rodapé --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">

            {{-- Coluna 1: Identidade --}}
            <div>
                @if($municipio->brasao_path)
                    <img src="{{ Storage::disk('r2')->url($municipio->brasao_path) }}"
                         alt="Brasão de {{ $municipio->nome }}"
                         class="h-16 w-auto object-contain mb-4 opacity-80">
                @endif
                <div class="text-white font-semibold text-lg">{{ $municipio->nome }}</div>
                @if($municipio->uf)
                    <div class="text-sm text-slate-500">Estado: {{ $municipio->uf }}</div>
                @endif
                @if($municipio->prefeito)
                    <div class="text-sm mt-2">Prefeito(a): <span class="text-slate-300">{{ $municipio->prefeito }}</span></div>
                @endif
                @if($municipio->site)
                    <a href="{{ $municipio->site }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1 text-sm text-blue-400 hover:text-blue-300 mt-3 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                        Site oficial da Prefeitura
                    </a>
                @endif
            </div>

            {{-- Coluna 2: Portal --}}
            <div>
                <h3 class="text-white font-semibold mb-4 text-sm uppercase tracking-wide">Portal dos Conselhos</h3>
                <ul class="space-y-2 text-sm">
                    <li>
                        <a href="{{ route('portal.index', $municipio->slug) }}"
                           class="hover:text-white transition-colors flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                            Todos os conselhos
                        </a>
                    </li>
                    <li>
                        <span class="flex items-center gap-2 text-slate-500 cursor-default">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/></svg>
                            Agenda de reuniões
                        </span>
                    </li>
                    <li class="pt-2 border-t border-slate-700">
                        <p class="text-xs text-slate-500 leading-relaxed">
                            As informações deste portal são de responsabilidade dos respectivos conselhos municipais.
                        </p>
                    </li>
                </ul>
            </div>

            {{-- Coluna 3: Transparência e legislação --}}
            <div>
                <h3 class="text-white font-semibold mb-4 text-sm uppercase tracking-wide">Transparência</h3>
                <ul class="space-y-2 text-sm">
                    <li>
                        <a href="https://www.planalto.gov.br/ccivil_03/_ato2011-2014/2011/lei/l12527.htm"
                           target="_blank" rel="noopener noreferrer"
                           class="hover:text-white transition-colors flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            Lei de Acesso à Informação (LAI)
                        </a>
                    </li>
                    <li>
                        <a href="https://www.planalto.gov.br/ccivil_03/_ato2019-2022/2018/lei/L13709.htm"
                           target="_blank" rel="noopener noreferrer"
                           class="hover:text-white transition-colors flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                            LGPD — Proteção de Dados
                        </a>
                    </li>
                    @if($municipio->site)
                    <li>
                        <a href="{{ $municipio->site }}"
                           target="_blank" rel="noopener noreferrer"
                           class="hover:text-white transition-colors flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253"/></svg>
                            Portal de Transparência Municipal
                        </a>
                    </li>
                    @endif
                    <li>
                        <span class="flex items-center gap-2 text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 011.037-.443 48.282 48.282 0 005.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/></svg>
                            Ouvidoria Municipal
                        </span>
                    </li>
                </ul>
            </div>

            {{-- Coluna 4: Contato e suporte --}}
            <div>
                <h3 class="text-white font-semibold mb-4 text-sm uppercase tracking-wide">Contato e Suporte</h3>
                <ul class="space-y-2 text-sm">
                    @if($municipio->email)
                    <li>
                        <a href="mailto:{{ $municipio->email }}"
                           class="hover:text-white transition-colors flex items-start gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mt-0.5 text-blue-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                            {{ $municipio->email }}
                        </a>
                    </li>
                    @endif
                    @if($municipio->telefone)
                    <li>
                        <a href="tel:{{ preg_replace('/\D/', '', $municipio->telefone) }}"
                           class="hover:text-white transition-colors flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                            {{ $municipio->telefone }}
                        </a>
                    </li>
                    @endif
                    @if($municipio->endereco)
                    <li class="flex items-start gap-2 text-slate-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                        <span>{{ $municipio->endereco }}{{ $municipio->cep ? ', CEP ' . $municipio->cep : '' }}</span>
                    </li>
                    @endif
                    <li class="pt-2 border-t border-slate-700">
                        <a href="mailto:{{ $municipio->email ?? 'contato@municipio.gov.br' }}?subject=Erro%20no%20Portal%20dos%20Conselhos&body=Página%3A%20{{ urlencode(request()->fullUrl()) }}%0A%0ADescrição%20do%20problema%3A%20"
                           class="hover:text-white transition-colors flex items-center gap-2 text-amber-500/80 hover:text-amber-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                            Reportar erro nesta página
                        </a>
                    </li>
                </ul>
            </div>

        </div>
    </div>

    {{-- Barra inferior do rodapé --}}
    <div class="border-t border-slate-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                <div class="flex items-center gap-4 flex-wrap justify-center sm:justify-start">
                    <span>Transparência e participação social — <abbr title="Lei de Acesso à Informação">Lei nº 12.527/2011</abbr></span>
                    <span class="hidden sm:inline text-slate-700">•</span>
                    <span>Sistema de Conselhos Municipais &copy; {{ date('Y') }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="a11yContraste()"
                            class="hover:text-slate-300 transition-colors"
                            title="Alto contraste"
                            aria-label="Alternar alto contraste">
                        Alto contraste
                    </button>
                    <span class="text-slate-700">•</span>
                    <span>
                        Fonte:
                        <button onclick="a11yFontSize(-2)" class="hover:text-slate-300 px-1" aria-label="Diminuir fonte">A-</button>
                        <button onclick="a11yFontSize(0)" class="hover:text-slate-300 px-1" aria-label="Fonte padrão">A</button>
                        <button onclick="a11yFontSize(2)" class="hover:text-slate-300 px-1" aria-label="Aumentar fonte">A+</button>
                    </span>
                </div>
            </div>
        </div>
    </div>
</footer>

{{-- ── Scripts de acessibilidade ───────────────────────────────────────── --}}
<script>
    var BASE_FONT = 16;
    var MIN_FONT  = 12;
    var MAX_FONT  = 24;

    function a11yFontSize(delta) {
        try {
            var current = parseInt(localStorage.getItem('a11y-fontsize') || BASE_FONT);
            var next = delta === 0 ? BASE_FONT : Math.min(MAX_FONT, Math.max(MIN_FONT, current + delta));
            document.documentElement.style.fontSize = next + 'px';
            localStorage.setItem('a11y-fontsize', next);
        } catch(e) {}
    }

    function a11yContraste() {
        try {
            var html = document.documentElement;
            var active = html.classList.toggle('alto-contraste');
            localStorage.setItem('a11y-contrast', active ? '1' : '0');
            var btn = document.getElementById('btn-contraste');
            if (btn) btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        } catch(e) {}
    }

    // Sincroniza estado do botão de contraste ao carregar
    (function() {
        try {
            var active = localStorage.getItem('a11y-contrast') === '1';
            var btn = document.getElementById('btn-contraste');
            if (btn) btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        } catch(e) {}
    })();
</script>

</body>
</html>
