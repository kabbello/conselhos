<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $municipio->nome . ' — Portal dos Conselhos Municipais')</title>
    <meta name="description" content="@yield('meta_description', 'Portal público dos Conselhos Municipais de ' . $municipio->nome)">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

    {{-- ── Barra superior / Header ─────────────────────────────────────────── --}}
    <header class="bg-blue-700 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Top bar --}}
            <div class="flex items-center justify-between py-4">
                <a href="{{ route('portal.index', $municipio->slug) }}" class="flex items-center gap-4 hover:opacity-90 transition-opacity">
                    @if($municipio->logo_path)
                        <img src="{{ Storage::url($municipio->logo_path) }}"
                             alt="Brasão de {{ $municipio->nome }}"
                             class="h-14 w-auto object-contain drop-shadow">
                    @else
                        <div class="h-14 w-14 rounded-full bg-white/20 flex items-center justify-center text-xl font-bold select-none">
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

                <nav class="hidden md:flex items-center gap-1 text-sm font-medium">
                    <a href="{{ route('portal.index', $municipio->slug) }}"
                       class="px-4 py-2 rounded-lg hover:bg-white/15 transition-colors @yield('nav_conselhos')">
                        Conselhos
                    </a>
                </nav>
            </div>

            {{-- Breadcrumb --}}
            @hasSection('breadcrumb')
            <div class="border-t border-blue-600/50 py-2">
                <nav class="flex items-center gap-2 text-sm text-blue-200">
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
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- ── Footer ──────────────────────────────────────────────────────────── --}}
    <footer class="bg-slate-800 text-slate-400 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="text-white font-semibold">{{ $municipio->nome }}</div>
                    <div class="text-sm mt-1">Portal dos Conselhos Municipais</div>
                </div>
                <div class="text-xs">
                    <p>Transparência e participação social — Lei nº 12.527/2011 (LAI)</p>
                    <p class="mt-1">Sistema de Conselhos Municipais &copy; {{ date('Y') }}</p>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
