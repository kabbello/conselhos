@extends('ajuda.layout')

@section('title', $title)
@section('meta_description', $description)

@section('content')

{{-- Breadcrumb --}}
<div class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <nav class="text-sm text-slate-500 flex items-center gap-1.5 flex-wrap" aria-label="Breadcrumb">
            <a href="{{ route('ajuda.index') }}" class="hover:text-blue-600 transition-colors">Central de Ajuda</a>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            <span class="hover:text-blue-600 transition-colors">{{ $secaoLabel }}</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            <span class="font-medium text-slate-700">{{ $title }}</span>
        </nav>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex gap-8">

        {{-- ── Sidebar ──────────────────────────────────────────────────────── --}}
        <aside class="hidden lg:block w-64 flex-shrink-0">
            {{-- Seção atual --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 bg-blue-700 text-white">
                    <p class="text-xs text-blue-300 uppercase tracking-wider font-medium">Seção</p>
                    <p class="font-semibold leading-tight">{{ $secaoLabel }}</p>
                </div>
                <nav class="py-2">
                    @foreach ($outrosTopicos as $t)
                    <a href="{{ route('ajuda.topico', [$secao, $t['slug']]) }}"
                       class="flex items-center gap-2 px-4 py-2.5 text-sm transition-colors
                              {{ $t['ativo'] ? 'bg-blue-50 text-blue-700 font-semibold border-r-2 border-blue-600' : 'text-slate-700 hover:bg-slate-50 hover:text-blue-600' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0 {{ $t['ativo'] ? 'text-blue-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                        </svg>
                        {{ $t['title'] }}
                    </a>
                    @endforeach
                </nav>
            </div>

            {{-- Outras seções --}}
            <div class="mt-4 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100">
                    <p class="text-xs text-slate-500 uppercase tracking-wider font-medium">Outras áreas</p>
                </div>
                <nav class="py-2">
                    @foreach ($todasSecoes as $slug => $info)
                    @if ($slug !== $secao)
                    <a href="{{ route('ajuda.index') }}#{{ $slug }}"
                       class="flex items-center gap-2 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                        {{ $info['label'] }}
                    </a>
                    @endif
                    @endforeach
                    <a href="{{ route('ajuda.index') }}"
                       class="flex items-center gap-2 px-4 py-2 text-sm text-blue-600 hover:bg-slate-50 font-medium transition-colors mt-1 border-t border-slate-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 15.75 3 12m0 0 3.75-3.75M3 12h18"/></svg>
                        Ver toda a ajuda
                    </a>
                </nav>
            </div>
        </aside>

        {{-- ── Conteúdo ─────────────────────────────────────────────────────── --}}
        <div class="flex-1 min-w-0">
            <article class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">

                {{-- Cabeçalho do artigo --}}
                <div class="px-8 pt-8 pb-4 border-b border-slate-100">
                    @if ($description)
                    <p class="text-sm text-blue-600 font-medium mb-1">{{ $secaoLabel }}</p>
                    @endif
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">{{ $title }}</h1>
                    @if ($description)
                    <p class="mt-2 text-slate-500 text-base">{{ $description }}</p>
                    @endif
                </div>

                {{-- Corpo do artigo (markdown renderizado) --}}
                <div class="px-8 py-6 prose-ajuda">
                    {!! $html !!}
                </div>

                {{-- Rodapé do artigo --}}
                <div class="px-8 py-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between gap-4 flex-wrap">
                    <p class="text-xs text-slate-400">Esta informação foi útil para você?</p>
                    <a href="{{ route('ajuda.index') }}"
                       class="text-sm text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 15.75 3 12m0 0 3.75-3.75M3 12h18"/></svg>
                        Voltar à Central de Ajuda
                    </a>
                </div>
            </article>

            {{-- Navegação entre tópicos --}}
            @php
                $current = collect($outrosTopicos)->firstWhere('ativo', true);
                $prev = null;
                $next = null;
                foreach ($outrosTopicos as $i => $t) {
                    if ($t['ativo']) {
                        $prev = $outrosTopicos[$i - 1] ?? null;
                        $next = $outrosTopicos[$i + 1] ?? null;
                    }
                }
            @endphp

            @if ($prev || $next)
            <div class="mt-4 grid grid-cols-2 gap-4">
                @if ($prev)
                <a href="{{ route('ajuda.topico', [$secao, $prev['slug']]) }}"
                   class="bg-white border border-slate-200 rounded-lg px-4 py-3 hover:border-blue-300 hover:shadow-sm transition-all group">
                    <p class="text-xs text-slate-400 mb-1 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 15.75 3 12m0 0 3.75-3.75M3 12h18"/></svg>
                        Anterior
                    </p>
                    <p class="text-sm font-medium text-slate-700 group-hover:text-blue-600 transition-colors">{{ $prev['title'] }}</p>
                </a>
                @else
                <div></div>
                @endif

                @if ($next)
                <a href="{{ route('ajuda.topico', [$secao, $next['slug']]) }}"
                   class="bg-white border border-slate-200 rounded-lg px-4 py-3 hover:border-blue-300 hover:shadow-sm transition-all text-right group">
                    <p class="text-xs text-slate-400 mb-1 flex items-center justify-end gap-1">
                        Próximo
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </p>
                    <p class="text-sm font-medium text-slate-700 group-hover:text-blue-600 transition-colors">{{ $next['title'] }}</p>
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

@endsection
