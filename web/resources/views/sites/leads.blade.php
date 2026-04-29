<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Leads · {{ $site->subdomain }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('sites.edit', $site) }}"
                   class="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50">Edit site</a>
                <a href="{{ route('sites.leads.csv', $site) }}"
                   class="px-3 py-1.5 text-sm bg-orange-600 hover:bg-orange-700 text-white rounded">Download CSV</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                @if ($leads->total() === 0)
                    <div class="p-10 text-center text-gray-600">
                        <p>No leads yet.</p>
                        <p class="text-sm mt-1">Share your site at
                            <a href="{{ $site->publicUrl() }}" class="text-orange-600 hover:underline">{{ $site->subdomain }}.{{ config('app.parent_domain') }}</a>
                            to start collecting interest.</p>
                    </div>
                @else
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left">
                            <tr class="text-xs uppercase tracking-wider text-gray-600">
                                <th class="px-4 py-3">When</th>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">All fields</th>
                                <th class="px-4 py-3">Form</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                        @foreach ($leads as $lead)
                            <tr class="align-top">
                                <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                                    <div>{{ $lead->created_at->diffForHumans() }}</div>
                                    <div class="text-xs text-gray-500">{{ $lead->created_at->format('M j, Y g:i a') }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-900">{{ $lead->name() ?: '—' }}</td>
                                <td class="px-4 py-3 text-gray-700">
                                    @if ($lead->email())
                                        <a href="mailto:{{ $lead->email() }}" class="text-orange-600 hover:underline">{{ $lead->email() }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <details class="text-xs text-gray-600">
                                        <summary class="cursor-pointer">{{ count($lead->payload_json ?? []) }} fields</summary>
                                        <dl class="mt-2 space-y-1">
                                            @foreach ($lead->payload_json ?? [] as $k => $v)
                                                <div><dt class="inline font-semibold">{{ $k }}:</dt>
                                                <dd class="inline">{{ is_array($v) ? json_encode($v) : $v }}</dd></div>
                                            @endforeach
                                        </dl>
                                    </details>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $lead->source_form ?? '—' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="p-4">
                        {{ $leads->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
