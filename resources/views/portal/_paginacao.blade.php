@if($registros->hasPages())
<div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
    <div class="text-sm text-slate-500">
        Mostrando <strong>{{ $registros->firstItem() }}</strong>–<strong>{{ $registros->lastItem() }}</strong>
        de <strong>{{ $registros->total() }}</strong> registros
    </div>
    <div class="flex items-center gap-1">
        {{-- Anterior --}}
        @if($registros->onFirstPage())
            <span class="px-3 py-2 text-sm text-slate-300 border border-slate-200 rounded-lg cursor-not-allowed">‹</span>
        @else
            <a href="{{ $registros->previousPageUrl() }}"
               class="px-3 py-2 text-sm text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">‹</a>
        @endif

        {{-- Páginas --}}
        @foreach($registros->getUrlRange(max(1, $registros->currentPage()-2), min($registros->lastPage(), $registros->currentPage()+2)) as $page => $url)
            @if($page == $registros->currentPage())
                <span class="px-3 py-2 text-sm font-semibold text-white bg-blue-600 border border-blue-600 rounded-lg">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="px-3 py-2 text-sm text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">{{ $page }}</a>
            @endif
        @endforeach

        {{-- Próxima --}}
        @if($registros->hasMorePages())
            <a href="{{ $registros->nextPageUrl() }}"
               class="px-3 py-2 text-sm text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">›</a>
        @else
            <span class="px-3 py-2 text-sm text-slate-300 border border-slate-200 rounded-lg cursor-not-allowed">›</span>
        @endif
    </div>
</div>
@endif
