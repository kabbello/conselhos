@php $meta = $activity->properties->get('_meta', []) @endphp
<div class="space-y-4 text-sm">

    <div class="grid grid-cols-2 gap-3">
        <div>
            <div class="text-xs font-semibold text-gray-400 uppercase mb-1">Data/Hora</div>
            <div>{{ $activity->created_at->format('d/m/Y H:i:s') }}</div>
        </div>
        <div>
            <div class="text-xs font-semibold text-gray-400 uppercase mb-1">Usuário</div>
            <div>{{ $activity->causer?->name ?? '—' }}</div>
        </div>
        <div>
            <div class="text-xs font-semibold text-gray-400 uppercase mb-1">Evento</div>
            <div>{{ $activity->event ?? '—' }}</div>
        </div>
        <div>
            <div class="text-xs font-semibold text-gray-400 uppercase mb-1">Entidade</div>
            <div>{{ class_basename($activity->subject_type ?? '') }} #{{ $activity->subject_id }}</div>
        </div>
        @if(!empty($meta['ip']))
        <div>
            <div class="text-xs font-semibold text-gray-400 uppercase mb-1">IP</div>
            <div class="font-mono">{{ $meta['ip'] }}</div>
        </div>
        @endif
        @if(!empty($meta['request_id']))
        <div>
            <div class="text-xs font-semibold text-gray-400 uppercase mb-1">Request ID</div>
            <div class="font-mono text-xs">{{ $meta['request_id'] }}</div>
        </div>
        @endif
        @if(!empty($meta['user_agent']))
        <div class="col-span-2">
            <div class="text-xs font-semibold text-gray-400 uppercase mb-1">User Agent</div>
            <div class="text-xs text-gray-500 truncate">{{ $meta['user_agent'] }}</div>
        </div>
        @endif
    </div>

    @php
        $old  = $activity->properties->get('old', []);
        $new  = $activity->properties->get('attributes', []);
        // _meta é metadado interno (IP, UA) — não exibir no diff de campos
        $keys = array_values(array_diff(
            array_unique(array_merge(array_keys($old), array_keys($new))),
            ['_meta']
        ));
    @endphp

    @if(!empty($keys))
    <div>
        <div class="text-xs font-semibold text-gray-400 uppercase mb-2">Campos alterados</div>
        <table class="w-full text-xs border border-gray-200 rounded-lg overflow-hidden">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Campo</th>
                    <th class="px-3 py-2 text-left font-semibold text-red-500">Antes</th>
                    <th class="px-3 py-2 text-left font-semibold text-green-600">Depois</th>
                </tr>
            </thead>
            <tbody>
                @foreach($keys as $key)
                <tr class="border-t border-gray-100">
                    <td class="px-3 py-1.5 font-mono text-gray-500">{{ $key }}</td>
                    <td class="px-3 py-1.5 text-red-600">
                        {{ is_array($old[$key] ?? null) ? json_encode($old[$key]) : ($old[$key] ?? '—') }}
                    </td>
                    <td class="px-3 py-1.5 text-green-700">
                        {{ is_array($new[$key] ?? null) ? json_encode($new[$key]) : ($new[$key] ?? '—') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @elseif($activity->properties->isNotEmpty())
    <div>
        <div class="text-xs font-semibold text-gray-400 uppercase mb-2">Propriedades</div>
        <pre class="bg-gray-50 rounded p-3 text-xs overflow-auto">{{ json_encode($activity->properties->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
    @endif

</div>
