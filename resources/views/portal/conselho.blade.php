@extends('portal.layout')

@section('title', $conselho->nome . ' — ' . $municipio->nome)

@section('meta_description', $conselho->descricao ?: 'Página pública do ' . $conselho->nome . ' de ' . $municipio->nome)

@section('breadcrumb')
    <svg class="h-4 w-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-white font-medium truncate">{{ $conselho->sigla ?: $conselho->nome }}</span>
@endsection

@section('content')

{{-- ── Hero do conselho ────────────────────────────────────────────────── --}}
<section class="bg-gradient-to-b from-blue-700 to-blue-600 text-white pt-2 pb-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center gap-6 mt-6">
            <div class="shrink-0 w-20 h-20 rounded-2xl bg-white/20 border border-white/30 flex items-center justify-center overflow-hidden">
                @if($conselho->logo_url)
                    <img src="{{ $conselho->logo_url }}" alt="{{ $conselho->sigla }}"
                         class="w-full h-full object-contain p-2">
                @else
                    <span class="text-white font-bold text-xl text-center p-2 leading-tight select-none">
                        {{ $conselho->sigla ?: strtoupper(substr($conselho->nome, 0, 3)) }}
                    </span>
                @endif
            </div>
            <div>
                <div class="text-blue-200 text-sm font-medium">
                    @if($conselho->tipo) {{ $conselho->tipo }} — @endif
                    Prefeitura Municipal de {{ $municipio->nome }}
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold mt-1 leading-tight">{{ $conselho->nome }}</h1>
                @if($conselho->email || $conselho->telefone || $conselho->endereco)
                <div class="mt-3 flex flex-wrap gap-4 text-sm text-blue-200">
                    @if($conselho->email && filter_var($conselho->email, FILTER_VALIDATE_EMAIL))
                    <a href="mailto:{{ $conselho->email }}" class="flex items-center gap-1.5 hover:text-white transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ $conselho->email }}
                    </a>
                    @endif
                    @if($conselho->telefone)
                    <span class="flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ $conselho->telefone }}
                    </span>
                    @endif
                    @if($conselho->endereco)
                    <span class="flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ $conselho->endereco }}
                    </span>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- ── Conteúdo principal ──────────────────────────────────────────────── --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    {{-- ── Coluna principal (2/3) ───────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-8">

        {{-- Descrição --}}
        @if($conselho->descricao)
        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h2 class="section-title text-blue-500">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Sobre o Conselho
            </h2>
            <p class="text-slate-600 leading-relaxed">{!! nl2br(e($conselho->descricao)) !!}</p>
        </section>
        @endif

        {{-- Próximas Reuniões --}}
        @if($reunioesAgendadas->isNotEmpty())
        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="section-title text-emerald-500 mb-0">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Próximas Reuniões
                </h2>
                @if($totalReunioesAgendadas > 5)
                <a href="{{ route('portal.reunioes', [$municipio->slug, $conselho->slug]) }}"
                   class="text-xs font-semibold text-blue-600 hover:text-blue-800 whitespace-nowrap">
                    Ver todas ({{ $totalReunioesAgendadas }}) →
                </a>
                @endif
            </div>
            <div class="space-y-4">
                @foreach($reunioesAgendadas as $reuniao)
                <div class="flex gap-4 p-4 bg-emerald-50 border border-emerald-100 rounded-xl">
                    {{-- Calendário --}}
                    <div class="shrink-0 text-center bg-white border border-emerald-200 rounded-xl px-3 py-2 min-w-[60px]">
                        <div class="text-xs font-semibold text-emerald-600 uppercase">{{ $reuniao->data_hora->locale('pt_BR')->isoFormat('MMM') }}</div>
                        <div class="text-2xl font-bold text-slate-800 leading-none">{{ $reuniao->data_hora->format('d') }}</div>
                        <div class="text-xs text-slate-500">{{ $reuniao->data_hora->format('Y') }}</div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-slate-800">
                            {{ $reuniao->tipoReuniao?->nome ?? 'Reunião' }}
                            <span class="text-slate-500 font-normal">— {{ $reuniao->data_hora->format('H:i') }}h</span>
                        </div>
                        @if($reuniao->local)
                        <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $reuniao->local }}
                        </div>
                        @endif
                        @if($reuniao->pauta)
                        @php $pautaLonga = mb_strlen($reuniao->pauta) > 150 @endphp
                        @if($pautaLonga)
                        <details class="mt-1.5 text-xs text-slate-600 leading-relaxed">
                            <summary class="cursor-pointer list-none">
                                {{ Str::limit($reuniao->pauta, 150) }}<span class="text-blue-500 ml-1">ver mais</span>
                            </summary>
                            <p class="mt-1 whitespace-pre-line">{{ $reuniao->pauta }}</p>
                        </details>
                        @else
                        <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">{{ $reuniao->pauta }}</p>
                        @endif
                        @endif
                        {{-- Links: apenas os marcados como audiência pública (C03) --}}
                        @php $linksPublicos = $reuniao->links->where('audiencia_publica', true) @endphp
                        @if($linksPublicos->isNotEmpty())
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($linksPublicos as $link)
                                @if($link->plataforma === 'Anexo')
                                <a href="{{ $link->url }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1.5 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-full px-3 py-1 transition-colors">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    Convocação
                                </a>
                                @else
                                <a href="{{ $link->url }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1.5 text-xs font-medium bg-emerald-600 hover:bg-emerald-700 text-white rounded-full px-3 py-1 transition-colors">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    {{ $link->plataforma ?? 'Entrar na reunião' }}
                                </a>
                                @endif
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Documentos --}}
        @if($documentos->isNotEmpty())
        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="section-title text-amber-500 mb-0">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Documentos
                </h2>
                @if($totalDocumentos > 5)
                <a href="{{ route('portal.documentos', [$municipio->slug, $conselho->slug]) }}"
                   class="text-xs font-semibold text-blue-600 hover:text-blue-800 whitespace-nowrap">
                    Ver todos ({{ $totalDocumentos }}) →
                </a>
                @endif
            </div>

            {{-- Agrupa por tipo --}}
            @foreach($documentos->groupBy(fn($d) => $d->tipoDocumento?->nome ?? 'Outros') as $tipo => $grupo)
            <div class="mb-5 last:mb-0">
                <div class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-2 pb-1 border-b border-slate-100">
                    {{ $tipo }} <span class="font-normal">({{ $grupo->count() }})</span>
                </div>
                <div class="space-y-1">
                    @foreach($grupo->sortByDesc('data_documento') as $doc)
                    <div class="flex items-center justify-between gap-3 py-2 px-3 rounded-lg hover:bg-slate-50 transition-colors group">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <svg class="shrink-0 h-4 w-4 text-slate-300 group-hover:text-amber-400 transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                            </svg>
                            <div class="min-w-0">
                                <div class="text-sm text-slate-700 truncate">{{ $doc->titulo ?? 'Documento sem título' }}</div>
                                @if($doc->data_documento)
                                <div class="text-xs text-slate-400">{{ $doc->data_documento->format('d/m/Y') }}</div>
                                @endif
                            </div>
                        </div>
                        @if($doc->url_download)
                        <a href="{{ $doc->url_download }}" target="_blank" rel="noopener"
                           class="shrink-0 inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors whitespace-nowrap">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Baixar
                        </a>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </section>
        @endif

        {{-- Legislação --}}
        @if($legislacao->isNotEmpty())
        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="section-title text-indigo-500 mb-0">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                    Legislação
                </h2>
                @if($totalLegislacao > 5)
                <a href="{{ route('portal.legislacao', [$municipio->slug, $conselho->slug]) }}"
                   class="text-xs font-semibold text-blue-600 hover:text-blue-800 whitespace-nowrap">
                    Ver toda ({{ $totalLegislacao }}) →
                </a>
                @endif
            </div>
            <div class="space-y-2">
                @foreach($legislacao->groupBy(fn($l) => $l->tipoLegislacao?->nome ?? 'Outros') as $tipo => $grupo)
                <div class="mb-4 last:mb-0">
                    <div class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-2 pb-1 border-b border-slate-100">{{ $tipo }}</div>
                    @foreach($grupo as $lei)
                    <div class="flex items-start justify-between gap-3 py-2 px-3 rounded-lg hover:bg-slate-50 transition-colors group">
                        <div class="min-w-0">
                            <div class="text-sm font-medium text-slate-800">
                                {{ $lei->titulo }}
                            </div>
                            @if($lei->numero)
                            <div class="text-xs text-slate-400 mt-0.5">Nº {{ $lei->numero }}</div>
                            @endif
                        </div>
                        @if($lei->link)
                        <a href="{{ $lei->link }}" target="_blank" rel="noopener"
                           class="shrink-0 inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors whitespace-nowrap mt-0.5">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Acessar
                        </a>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Atos Normativos --}}
        @if($atosNormativos->isNotEmpty())
        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="section-title text-violet-500 mb-0">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Atos Normativos
                </h2>
                @if($totalAtosNormativos > 5)
                <a href="{{ route('portal.atos-normativos', [$municipio->slug, $conselho->slug]) }}"
                   class="text-xs font-semibold text-blue-600 hover:text-blue-800 whitespace-nowrap">
                    Ver todos ({{ $totalAtosNormativos }}) →
                </a>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="text-left py-2 pr-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Identificação</th>
                            <th class="text-left py-2 pr-4 text-xs font-semibold text-slate-400 uppercase tracking-wider hidden sm:table-cell">Ementa</th>
                            <th class="text-left py-2 pr-2 text-xs font-semibold text-slate-400 uppercase tracking-wider hidden md:table-cell">Publicação</th>
                            <th class="text-left py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($atosNormativos as $ato)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 pr-4">
                                <div class="font-semibold text-slate-800">{{ $ato->label_tipo }}</div>
                                <div class="text-xs text-slate-500">nº {{ $ato->numero_completo }}</div>
                            </td>
                            <td class="py-3 pr-4 hidden sm:table-cell">
                                <p class="text-xs text-slate-600 leading-relaxed">
                                    {{ $ato->ementa ? Str::limit($ato->ementa, 100) : ($ato->titulo ? Str::limit($ato->titulo, 100) : '—') }}
                                </p>
                            </td>
                            <td class="py-3 pr-2 hidden md:table-cell text-xs text-slate-500 whitespace-nowrap">
                                {{ $ato->data_publicacao?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="py-3">
                                @if($ato->status === 'VIGENTE')
                                    <span class="badge bg-emerald-100 text-emerald-700">Vigente</span>
                                @elseif($ato->status === 'REVOGADO')
                                    <span class="badge bg-red-100 text-red-600">Revogado</span>
                                @else
                                    <span class="badge bg-slate-100 text-slate-600">{{ ucfirst(strtolower($ato->status)) }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        @endif

        {{-- Reuniões Realizadas --}}
        @if($reunioesRealizadas->isNotEmpty())
        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="section-title text-slate-400 mb-0">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    Reuniões Realizadas
                </h2>
                @if($totalReunioesRealizadas > 5)
                <a href="{{ route('portal.reunioes', [$municipio->slug, $conselho->slug, 'status' => 'realizada']) }}"
                   class="text-xs font-semibold text-blue-600 hover:text-blue-800 whitespace-nowrap">
                    Ver todas ({{ $totalReunioesRealizadas }}) →
                </a>
                @endif
            </div>
            <div class="space-y-1">
                @foreach($reunioesRealizadas as $reuniao)
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 py-2 border-b border-slate-50 last:border-0 text-sm">
                    <span class="text-xs font-medium text-slate-400 w-20 shrink-0 text-right">
                        {{ $reuniao->data_hora->format('d/m/Y') }}
                    </span>
                    <span class="text-slate-700">
                        {{ $reuniao->tipoReuniao?->nome ?? 'Reunião' }}
                        @if($reuniao->local)
                            <span class="text-slate-400 text-xs">— {{ $reuniao->local }}</span>
                        @endif
                    </span>
                    {{-- Links de ata/anexo --}}
                    @foreach($reuniao->links->where('plataforma', 'Anexo') as $link)
                    <a href="{{ $link->url }}" target="_blank" rel="noopener"
                       class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        Anexo
                    </a>
                    @endforeach
                </div>
                @endforeach
            </div>
        </section>
        @endif

    </div>

    {{-- ── Coluna lateral (1/3) ─────────────────────────────────────────── --}}
    <div class="space-y-6">

        {{-- Diretoria --}}
        @if($gestores->isNotEmpty())
        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h2 class="section-title text-blue-500">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Diretoria
            </h2>
            <div class="space-y-4">
                @foreach($gestores as $membro)
                <div class="flex items-start gap-3">
                    <div class="shrink-0 w-9 h-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-bold select-none">
                        {{ strtoupper(substr($membro->nome_exibicao ?? $membro->conselheiro?->nome ?? '?', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-slate-800 truncate">
                            {{ $membro->nome_exibicao ?? $membro->conselheiro?->nome }}
                        </div>
                        <div class="text-xs text-slate-500">
                            {{ match($membro->tipo) {
                                'PRESIDENTE'     => 'Presidente',
                                'VICE_PRESIDENTE'=> 'Vice-Presidente',
                                'SECRETARIO'     => 'Secretário(a)',
                                default          => ucfirst(strtolower($membro->tipo))
                            } }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Composição --}}
        <section class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h2 class="section-title text-slate-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Composição
                <span class="ml-auto text-xs font-normal text-slate-400 normal-case tracking-normal">
                    {{ $membros->count() }} membro{{ $membros->count() !== 1 ? 's' : '' }}
                </span>
            </h2>

            @if($membros->isEmpty())
                <p class="text-sm text-slate-400 text-center py-4">Nenhum membro cadastrado.</p>
            @else
            <div class="space-y-0.5">
                @foreach($membros->groupBy('tipo') as $tipo => $grupoMembros)
                <div class="mt-3 first:mt-0">
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                        {{ match($tipo) {
                            'PRESIDENTE'      => 'Presidente',
                            'VICE_PRESIDENTE' => 'Vice-Presidente',
                            'SECRETARIO'      => 'Secretário(a)',
                            'MEMBRO'          => 'Membros Titulares',
                            'SUPLENTE'        => 'Suplentes',
                            default           => ucfirst(strtolower(str_replace('_', ' ', $tipo)))
                        } }}
                    </div>
                    @foreach($grupoMembros as $membro)
                    <div class="flex items-center gap-2 py-1.5 border-b border-slate-50 last:border-0">
                        <div class="shrink-0 w-6 h-6 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center text-xs font-semibold select-none">
                            {{ strtoupper(substr($membro->nome_exibicao ?? $membro->conselheiro?->nome ?? '?', 0, 1)) }}
                        </div>
                        <span class="text-sm text-slate-700 truncate">
                            {{ $membro->nome_exibicao ?? $membro->conselheiro?->nome ?? '—' }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
            @endif
        </section>

        {{-- Voltar --}}
        <a href="{{ route('portal.index', $municipio->slug) }}"
           class="flex items-center justify-center gap-2 w-full py-3 px-4 border border-slate-200 hover:border-blue-300 hover:text-blue-600 text-slate-600 text-sm font-medium rounded-xl transition-colors bg-white">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Ver todos os conselhos
        </a>
    </div>

</div>
</div>

@endsection

@push('styles')
<style>
.section-title {
    @apply text-base font-bold text-slate-700 uppercase tracking-wider mb-4 flex items-center gap-2;
}
.badge {
    @apply inline-block text-xs font-semibold rounded-full px-2.5 py-0.5;
}
</style>
@endpush
