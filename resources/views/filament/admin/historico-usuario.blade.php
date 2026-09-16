<div class="space-y-2 max-h-[60vh] overflow-y-auto py-1">
    @forelse ($logs as $log)
        <div class="flex gap-3 rounded-lg border border-gray-200 dark:border-gray-700 p-3 text-sm">
            <div class="mt-0.5 shrink-0">
                @if ($log->log_name === 'impersonation')
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300">
                        <x-heroicon-o-identification class="w-4 h-4"/>
                    </span>
                @else
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300">
                        <x-heroicon-o-pencil-square class="w-4 h-4"/>
                    </span>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-medium text-gray-900 dark:text-white">
                    {{ $log->description }}
                </p>
                @if ($log->log_name === 'impersonation')
                    <p class="text-gray-500 dark:text-gray-400 text-xs mt-0.5">
                        Por: {{ $log->properties['impersonator_email'] ?? '—' }}
                        @if (!empty($log->properties['motivo']))
                            · Motivo: {{ $log->properties['motivo'] }}
                        @endif
                    </p>
                @elseif ($log->properties->get('old') || $log->properties->get('attributes'))
                    <div class="mt-1 space-y-0.5 text-xs text-gray-500 dark:text-gray-400">
                        @foreach (($log->properties['attributes'] ?? []) as $field => $novo)
                            @php $antigo = $log->properties['old'][$field] ?? null; @endphp
                            <span class="inline-block">
                                <span class="font-mono bg-gray-100 dark:bg-gray-800 px-1 rounded">{{ $field }}</span>:
                                @if ($antigo !== null)
                                    <span class="line-through text-red-500">{{ Str::limit((string) $antigo, 40) }}</span>
                                    →
                                @endif
                                <span class="text-green-600 dark:text-green-400">{{ Str::limit((string) $novo, 40) }}</span>
                            </span>
                        @endforeach
                    </div>
                @endif
                <p class="text-gray-400 dark:text-gray-500 text-xs mt-1">
                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                </p>
            </div>
        </div>
    @empty
        <p class="text-center text-gray-500 dark:text-gray-400 py-8">Nenhuma atividade registrada.</p>
    @endforelse
</div>
