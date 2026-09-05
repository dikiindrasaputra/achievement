@foreach ($manpower as $person)
    @php
        $remainingQuota = \App\Models\Whitelist::getRemainingQuota($person->id, $weekStart);
        $whitelistDates = $person->whitelists->keyBy(fn($w) => $w->date->format('Y-m-d'));
    @endphp
    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700" data-manpower-row>
        <td class="px-4 py-3 sticky left-0 bg-white dark:bg-gray-800 z-10">
            <div class="text-sm font-mono text-gray-500">{{ $person->nip }}</div>
            <div class="text-sm font-medium">{{ $person->full_name }}</div>
            <div class="text-xs text-gray-400">
                <span class="px-1.5 inline-flex text-[10px] leading-5 font-semibold rounded-full {{ $person->contract_type == 'dedicated' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                    {{ ucfirst($person->contract_type) }}
                </span>
            </div>
        </td>
        <td class="px-4 py-3 text-center">
            <span class="text-sm font-semibold {{ $remainingQuota > 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ $remainingQuota }}
            </span>
            <span class="text-xs text-gray-400">/{{ $person->contract_type == 'dedicated' ? '1' : '3' }}</span>
        </td>
        @foreach ($weekDays as $day)
            @php
                $isWhitelisted = $whitelistDates->has($day['date']);
                $whitelistRecord = $isWhitelisted ? $whitelistDates->get($day['date']) : null;
            @endphp
            <td class="px-4 py-3 text-center {{ $day['is_today'] ? 'bg-indigo-50 dark:bg-indigo-900/30' : '' }}">
                @if ($isWhitelisted)
                    <button type="button"
                        @click="showModal = true; modalName = '{{ $person->full_name }}'; modalDate = '{{ $day['day_name'] }} {{ $day['date_display'] }}'; modalAction = '{{ route('whitelists.destroy', $whitelistRecord) }}'"
                        class="w-8 h-8 rounded-full bg-green-500 text-white hover:bg-red-500 transition-colors inline-flex items-center justify-center cursor-pointer" title="Klik untuk hapus">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </button>
                @elseif ($day['is_past'])
                    <span class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 inline-flex items-center justify-center text-gray-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                    </span>
                @elseif ($remainingQuota > 0)
                    <form action="{{ route('whitelists.store') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="manpower_id" value="{{ $person->id }}">
                        <input type="hidden" name="date" value="{{ $day['date'] }}">
                        <button type="submit" class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 hover:bg-green-500 hover:text-white transition-colors inline-flex items-center justify-center" title="Klik untuk whitelist">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </button>
                    </form>
                @else
                    <span class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900 inline-flex items-center justify-center text-red-400" title="Quota habis">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"></path></svg>
                    </span>
                @endif
            </td>
        @endforeach
    </tr>
@endforeach
