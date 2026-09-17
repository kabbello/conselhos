@extends('portal.layout')

@section('title', 'Conselhos Municipais — ' . $municipio->nome)

@section('nav_conselhos', 'bg-white/15')

@section('content')

{{-- ── Hero ──────────────────────────────────────────────────────────────── --}}
<section class="bg-gradient-to-b from-blue-700 to-blue-600 text-white pt-2 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight mt-6">
            Conselhos Municipais
        </h1>
        <p class="mt-3 text-blue-100 max-w-2xl mx-auto text-base sm:text-lg">
            Órgãos colegiados de controle social que garantem a participação da sociedade civil
            na formulação e fiscalização das políticas públicas municipais.
        </p>

        @if($conselhos->count())
        <div class="mt-8 flex flex-wrap justify-center gap-6 text-sm">
            <div class="bg-white/15 rounded-xl px-6 py-3 text-center">
                <div class="text-2xl font-bold">{{ $conselhos->count() }}</div>
                <div class="text-blue-200 font-medium">Conselhos Ativos</div>
            </div>
            <div class="bg-white/15 rounded-xl px-6 py-3 text-center">
                <div class="text-2xl font-bold">
                    {{ $conselhos->sum('membros_ativos_count') }}
                </div>
                <div class="text-blue-200 font-medium">Conselheiros</div>
            </div>
            <div class="bg-white/15 rounded-xl px-6 py-3 text-center">
                <div class="text-2xl font-bold">
                    {{ $conselhos->sum('reunioes_agendadas_count') }}
                </div>
                <div class="text-blue-200 font-medium">Reuniões Agendadas</div>
            </div>
            <div class="bg-white/15 rounded-xl px-6 py-3 text-center">
                <div class="text-2xl font-bold">
                    {{ $conselhos->sum('atos_vigentes_count') }}
                </div>
                <div class="text-blue-200 font-medium">Atos Normativos Vigentes</div>
            </div>
        </div>
        @endif
    </div>
</section>

{{-- ── Grid de cards ────────────────────────────────────────────────────── --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    @if($conselhos->isNotEmpty())
    {{-- Busca de conselho --}}
    <div class="mb-8">
        <div class="relative max-w-md">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="search" id="busca-conselho"
                   placeholder="Buscar conselho por nome ou sigla..."
                   aria-label="Buscar conselho"
                   class="w-full pl-9 pr-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
        </div>
        <p id="busca-sem-resultado" class="hidden mt-4 text-sm text-slate-500">Nenhum conselho encontrado para esta busca.</p>
    </div>
    @endif

    @if($conselhos->isEmpty())
        <div class="text-center py-20 text-slate-500">
            <svg class="mx-auto h-14 w-14 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <p class="font-medium text-lg">Nenhum conselho cadastrado ainda.</p>
        </div>
    @else

    {{-- Agrupados por tipo --}}
    @foreach($conselhos->groupBy('tipo') as $tipo => $grupo)

        @if($conselhos->groupBy('tipo')->count() > 1)
        <h2 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4 mt-10 first:mt-0 border-b border-slate-200 pb-2"
            data-grupo="{{ $tipo ?: 'Outros' }}">
            {{ $tipo ?: 'Outros' }}
        </h2>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($grupo as $conselho)
            <article class="bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden group"
                     data-busca="{{ strtolower($conselho->nome . ' ' . ($conselho->sigla ?? '')) }}"
                     data-grupo="{{ $tipo ?: 'Outros' }}">

                {{-- Faixa colorida superior por tipo --}}
                <div class="h-1.5 {{ $loop->parent->index % 5 === 0 ? 'bg-blue-500' : ($loop->parent->index % 5 === 1 ? 'bg-emerald-500' : ($loop->parent->index % 5 === 2 ? 'bg-violet-500' : ($loop->parent->index % 5 === 3 ? 'bg-amber-500' : 'bg-rose-500'))) }}"></div>

                <div class="p-6 flex flex-col flex-1">

                    {{-- Cabeçalho: logo/sigla + nome --}}
                    <div class="flex items-start gap-4">
                        <div class="shrink-0 w-14 h-14 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center overflow-hidden">
                            @if($conselho->logo_url)
                                <img src="{{ $conselho->logo_url }}" alt="{{ $conselho->sigla }}"
                                     class="w-full h-full object-contain p-1">
                            @else
                                <span class="text-blue-700 font-bold text-sm text-center leading-tight p-1 select-none">
                                    {{ $conselho->sigla ?: strtoupper(substr($conselho->nome, 0, 3)) }}
                                </span>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <h3 class="font-semibold text-slate-800 leading-snug text-base group-hover:text-blue-700 transition-colors">
                                {{ $conselho->nome }}
                            </h3>
                            @if($conselho->tipo)
                            <span class="inline-block mt-1 text-xs font-medium text-slate-500 bg-slate-100 rounded-full px-2.5 py-0.5">
                                {{ $conselho->tipo }}
                            </span>
                            @endif
                        </div>
                    </div>

                    {{-- Descrição --}}
                    @if($conselho->descricao)
                    <p class="mt-4 text-sm text-slate-600 leading-relaxed line-clamp-3">
                        {{ $conselho->descricao }}
                    </p>
                    @endif

                    {{-- Indicadores --}}
                    <div class="mt-4 flex flex-wrap gap-3">
                        <div class="flex items-center gap-1.5 text-xs text-slate-500">
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span><strong class="font-semibold text-slate-700">{{ $conselho->membros_ativos_count }}</strong> conselheiros</span>
                        </div>

                        @if($conselho->reunioes_agendadas_count > 0)
                        <div class="flex items-center gap-1.5 text-xs text-emerald-600 bg-emerald-50 rounded-full px-2.5 py-1">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>{{ $conselho->reunioes_agendadas_count }} reunião{{ $conselho->reunioes_agendadas_count > 1 ? 'ões' : '' }} agendada{{ $conselho->reunioes_agendadas_count > 1 ? 's' : '' }}</span>
                        </div>
                        @endif

                        @if($conselho->atos_vigentes_count > 0)
                        <div class="flex items-center gap-1.5 text-xs text-violet-600 bg-violet-50 rounded-full px-2.5 py-1">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>{{ $conselho->atos_vigentes_count }} ato{{ $conselho->atos_vigentes_count > 1 ? 's' : '' }} vigente{{ $conselho->atos_vigentes_count > 1 ? 's' : '' }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Contato --}}
                    @if($conselho->email || $conselho->telefone)
                    <div class="mt-4 pt-4 border-t border-slate-100 flex flex-wrap gap-3 text-xs text-slate-500">
                        @if($conselho->email && filter_var($conselho->email, FILTER_VALIDATE_EMAIL))
                        <a href="mailto:{{ $conselho->email }}"
                           class="flex items-center gap-1.5 hover:text-blue-600 transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ $conselho->email }}
                        </a>
                        @endif
                        @if($conselho->telefone)
                        <span class="flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            {{ $conselho->telefone }}
                        </span>
                        @endif
                    </div>
                    @endif

                    {{-- CTA --}}
                    <div class="mt-6 pt-4 border-t border-slate-100">
                        <a href="{{ route('portal.conselho', [$municipio->slug, $conselho->slug]) }}"
                           class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors">
                            Ver página do conselho
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </article>
            @endforeach
        </div>

    @endforeach

    @endif

</section>

@endsection

@push('scripts')
<script>
(function () {
    var input = document.getElementById('busca-conselho');
    if (!input) return;
    var semResultado = document.getElementById('busca-sem-resultado');

    input.addEventListener('input', function () {
        var q = this.value.trim().toLowerCase();
        var cards = document.querySelectorAll('article[data-busca]');
        var visiveis = 0;

        cards.forEach(function (card) {
            var match = !q || card.dataset.busca.includes(q);
            card.style.display = match ? '' : 'none';
            if (match) visiveis++;
        });

        // Esconde/mostra cabeçalhos de grupo quando todos os cards do grupo estão ocultos
        document.querySelectorAll('h2[data-grupo]').forEach(function (h2) {
            var grupo = h2.dataset.grupo;
            var temVisivel = Array.from(document.querySelectorAll('article[data-grupo="' + grupo + '"]'))
                .some(function (a) { return a.style.display !== 'none'; });
            h2.style.display = temVisivel ? '' : 'none';
        });

        if (semResultado) semResultado.classList.toggle('hidden', visiveis > 0 || !q);
    });
})();
</script>
@endpush
