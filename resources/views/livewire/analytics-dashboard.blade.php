<div class="py-10">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">{{ __('Analytics') }}</h1>
                <p class="mt-1 text-gray-600">{{ __('Klicks auf deine Smart-Links (letzte Tage).') }}</p>
            </div>
            <div>
                <label class="text-sm text-gray-500" for="range">{{ __('Zeitraum') }}</label>
                <select wire:model.live="rangeDays" id="range" class="mt-1 block w-full sm:w-40 border-gray-300 rounded-md shadow-sm">
                    <option value="7">7</option>
                    <option value="30">30</option>
                    <option value="90">90</option>
                </select>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="bg-white overflow-hidden shadow sm:rounded-lg p-6">
                <div class="text-sm text-gray-500">{{ __('Klicks gesamt') }}</div>
                <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $total }}</div>
            </div>
        </div>

        <div class="bg-white shadow sm:rounded-lg p-6">
            <h2 class="font-medium text-gray-900 mb-4">{{ __('Klicks pro Tag') }}</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500">
                            <th class="py-2 pr-4">{{ __('Datum') }}</th>
                            <th class="py-2">{{ __('Klicks') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($perDay as $row)
                            <tr class="border-t border-gray-100">
                                <td class="py-2 pr-4">{{ $row->d }}</td>
                                <td class="py-2">{{ $row->c }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="py-6 text-center text-gray-500">{{ __('Noch keine Daten im Zeitraum.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow sm:rounded-lg p-6">
            <h2 class="font-medium text-gray-900 mb-4">{{ __('Top-Links') }}</h2>
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="py-2 pr-4">{{ __('Link') }}</th>
                        <th class="py-2">{{ __('Klicks') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($topLinks as $row)
                        <tr class="border-t border-gray-100">
                            <td class="py-2 pr-4">{{ $row->title }}</td>
                            <td class="py-2">{{ $row->clicks }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="py-6 text-center text-gray-500">{{ __('Keine Daten') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
