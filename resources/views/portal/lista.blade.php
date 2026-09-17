@extends('portal.layout')

@section('title', $titulo . ' — ' . $conselho->nome . ' — ' . $municipio->nome)

@section('breadcrumb')
    <svg class="h-4 w-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <a href="{{ route('portal.conselho', [$municipio->slug, $conselho->slug]) }}"
       class="hover:text-white transition-colors">
        {{ $conselho->sigla ?: Str::limit($conselho->nome, 30) }}
    </a>
    <svg class="h-4 w-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-white font-medium">{{ $titulo }}</span>
@endsection

@section('content')

{{-- ── Sub-header ──────────────────────────────────────────────────────── --}}
<div class="bg-blue-600 pb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-4">
            @if($conselho->logo_url)
            <img src="{{ $conselho->logo_url }}" alt="{{ $conselho->sigla }}"
                 class="h-12 w-12 rounded-xl object-contain bg-white/10 p-1">
            @endif
            <div>
                <div class="text-blue-200 text-xs font-medium">{{ $conselho->nome }}</div>
                <h1 class="text-xl font-bold text-white">{{ $titulo }}</h1>
            </div>
        </div>
    </div>
</div>

{{-- ── Conteúdo ─────────────────────────────────────────────────────────── --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Barra de busca e filtros --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <div class="flex-1 min-w-[200px]">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="busca" value="{{ $busca ?? '' }}"
                       placeholder="Buscar em {{ strtolower($titulo) }}..."
                       class="w-full pl-9 pr-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
            </div>
        </div>

        {{-- Filtro por tipo (documentos / legislação / atos) --}}
        @if($secao === 'documentos' && isset($tipos) && $tipos->isNotEmpty())
        <select name="tipo" class="px-3 py-2.5 text-sm border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todos os tipos</option>
            @foreach($tipos as $t)
            <option value="{{ $t->id }}" {{ request('tipo') == $t->id ? 'selected' : '' }}>{{ $t->nome }}</option>
            @endforeach
        </select>
        @endif

        @if($secao === 'legislacao' && isset($tipos) && $tipos->isNotEmpty())
        <select name="tipo" class="px-3 py-2.5 text-sm border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todos os tipos</option>
            @foreach($tipos as $t)
            <option value="{{ $t->id }}" {{ request('tipo') == $t->id ? 'selected' : '' }}>{{ $t->nome }}</option>
            @endforeach
        </select>
        @endif

        @if($secao === 'atos-normativos')
        <select name="tipo" class="px-3 py-2.5 text-sm border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todos os tipos</option>
            @foreach(['RESOLUCAO'=>'Resolução','DELIBERACAO'=>'Deliberação','RECOMENDACAO'=>'Recomendação','PARECER'=>'Parecer','MOCAO'=>'Moção','PORTARIA'=>'Portaria'] as $val => $label)
            <option value="{{ $val }}" {{ request('tipo') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status" class="px-3 py-2.5 text-sm border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todos os status</option>
            @foreach(['VIGENTE'=>'Vigente','APROVADO'=>'Aprovado','REVOGADO'=>'Revogado'] as $val => $label)
            <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @endif

        @if($secao === 'reunioes')
        <select name="status" class="px-3 py-2.5 text-sm border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todas as reuniões</option>
            <option value="agendada" {{ ($status ?? '') === 'agendada' ? 'selected' : '' }}>Agendadas</option>
            <option value="realizada" {{ ($status ?? '') === 'realizada' ? 'selected' : '' }}>Realizadas</option>
            <option value="cancelada" {{ ($status ?? '') === 'cancelada' ? 'selected' : '' }}>Canceladas</option>
        </select>
        @endif

        <button type="submit"
                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors">
            Buscar
        </button>

        @if($busca || request()->hasAny(['tipo','status']))
        <a href="{{ route('portal.' . $secao, [$municipio->slug, $conselho->slug]) }}"
           class="px-4 py-2.5 border border-slate-200 hover:border-slate-300 text-slate-600 text-sm rounded-xl transition-colors bg-white">
            Limpar
        </a>
        @endif
    </form>

    {{-- ── Lista: Documentos ──────────────────────────────────────────────── --}}
    @if($secao === 'documentos')
        @if($documentos->isEmpty())
            @include('portal._vazio', ['msg' => 'Nenhum documento encontrado.'])
        @else
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="divide-y divide-slate-50">
                @foreach($documentos as $doc)
                <div class="flex items-center justify-between gap-4 px-5 py-4 hover:bg-slate-50 transition-colors">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="shrink-0 w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center">
                            <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-medium text-slate-800 truncate">{{ $doc->titulo ?? 'Sem título' }}</div>
                            <div class="text-xs text-slate-400 mt-0.5 flex items-center gap-2">
                                @if($doc->tipoDocumento) <span>{{ $doc->tipoDocumento->nome }}</span> @endif
                                @if($doc->data_documento) <span>&middot; {{ $doc->data_documento->format('d/m/Y') }}</span> @endif
                            </div>
                        </div>
                    </div>
                    @if($doc->url_download)
                    <a href="{{ $doc->url_download }}" target="_blank" rel="noopener"
                       class="shrink-0 inline-flex items-center gap-1.5 text-xs font-semibold bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg px-3 py-1.5 transition-colors">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Baixar
                    </a>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @include('portal._paginacao', ['registros' => $documentos])
        @endif

    {{-- ── Lista: Legislação ──────────────────────────────────────────────── --}}
    @elseif($secao === 'legislacao')
        @if($legislacao->isEmpty())
            @include('portal._vazio', ['msg' => 'Nenhuma legislação encontrada.'])
        @else
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="divide-y divide-slate-50">
                @foreach($legislacao as $lei)
                <div class="flex items-start justify-between gap-4 px-5 py-4 hover:bg-slate-50 transition-colors">
                    <div class="min-w-0">
                        <div class="text-sm font-medium text-slate-800">{{ $lei->titulo }}</div>
                        <div class="text-xs text-slate-400 mt-0.5 flex items-center gap-2">
                            @if($lei->tipoLegislacao) <span>{{ $lei->tipoLegislacao->nome }}</span> @endif
                            @if($lei->numero) <span>&middot; Nº {{ $lei->numero }}</span> @endif
                        </div>
                    </div>
                    @if($lei->link)
                    <a href="{{ $lei->link }}" target="_blank" rel="noopener"
                       class="shrink-0 inline-flex items-center gap-1.5 text-xs font-semibold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg px-3 py-1.5 transition-colors">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Acessar
                    </a>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @include('portal._paginacao', ['registros' => $legislacao])
        @endif

    {{-- ── Lista: Reuniões ────────────────────────────────────────────────── --}}
    @elseif($secao === 'reunioes')
        @if($reunioes->isEmpty())
            @include('portal._vazio', ['msg' => 'Nenhuma reunião encontrada.'])
        @else
        <div class="space-y-3">
            @foreach($reunioes as $reuniao)
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm px-5 py-4 flex gap-4">
                {{-- Data --}}
                <div class="shrink-0 text-center bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 min-w-[56px]">
                    <div class="text-xs font-semibold {{ $reuniao->status === 'agendada' ? 'text-emerald-600' : 'text-slate-400' }} uppercase">
                        {{ $reuniao->data_hora->locale('pt_BR')->isoFormat('MMM') }}
                    </div>
                    <div class="text-xl font-bold text-slate-800 leading-none">{{ $reuniao->data_hora->format('d') }}</div>
                    <div class="text-xs text-slate-400">{{ $reuniao->data_hora->format('Y') }}</div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm font-semibold text-slate-800">
                            {{ $reuniao->tipoReuniao?->nome ?? 'Reunião' }}
                            <span class="font-normal text-slate-500">— {{ $reuniao->data_hora->format('H:i') }}h</span>
                        </span>
                        @if($reuniao->status === 'agendada')
                            <span class="badge bg-emerald-100 text-emerald-700">Agendada</span>
                        @elseif($reuniao->status === 'realizada')
                            <span class="badge bg-slate-100 text-slate-600">Realizada</span>
                        @else
                            <span class="badge bg-red-100 text-red-600">Cancelada</span>
                        @endif
                    </div>
                    @if($reuniao->local)
                    <div class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ $reuniao->local }}
                    </div>
                    @endif
                    @if($reuniao->pauta)
                    @php $pautaLonga = mb_strlen($reuniao->pauta) > 200 @endphp
                    @if($pautaLonga)
                    <details class="mt-1.5 text-xs text-slate-600 leading-relaxed">
                        <summary class="cursor-pointer list-none">
                            {{ Str::limit($reuniao->pauta, 200) }}<span class="text-blue-500 ml-1">ver mais</span>
                        </summary>
                        <p class="mt-1 whitespace-pre-line">{{ $reuniao->pauta }}</p>
                    </details>
                    @else
                    <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">{{ $reuniao->pauta }}</p>
                    @endif
                    @endif
                    @if($reuniao->links->isNotEmpty())
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach($reuniao->links as $link)
                            @if($link->plataforma === 'Anexo')
                            <a href="{{ $link->url }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1.5 text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-full px-3 py-1 transition-colors">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                Convocação / Ata
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
        @include('portal._paginacao', ['registros' => $reunioes])
        @endif

    {{-- ── Lista: Atos Normativos ─────────────────────────────────────────── --}}
    @elseif($secao === 'atos-normativos')
        @if($atosNormativos->isEmpty())
            @include('portal._vazio', ['msg' => 'Nenhum ato normativo encontrado.'])
        @else
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Identificação</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden sm:table-cell">Ementa</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden md:table-cell">Publicação</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($atosNormativos as $ato)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3">
                            <div class="font-semibold text-slate-800">{{ $ato->label_tipo }}</div>
                            <div class="text-xs text-slate-500">nº {{ $ato->numero_completo }}</div>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <p class="text-xs text-slate-600 leading-relaxed">
                                {{ $ato->ementa ? Str::limit($ato->ementa, 120) : ($ato->titulo ? Str::limit($ato->titulo, 120) : '—') }}
                            </p>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell text-xs text-slate-500 whitespace-nowrap">
                            {{ $ato->data_publicacao?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
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
        @include('portal._paginacao', ['registros' => $atosNormativos])
        @endif
    @endif

    {{-- Voltar ao conselho --}}
    <div class="mt-8">
        <a href="{{ route('portal.conselho', [$municipio->slug, $conselho->slug]) }}"
           class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-blue-600 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Voltar para {{ $conselho->sigla ?: 'o conselho' }}
        </a>
    </div>
</div>

@endsection

@push('styles')
<style>
.badge { @apply inline-block text-xs font-semibold rounded-full px-2.5 py-0.5; }
</style>
@endpush
