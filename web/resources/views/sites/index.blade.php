<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Your sites</h2>
            <a href="{{ route('sites.create') }}"
               class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-md shadow-sm">
                + New site
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-2 rounded">
                    {{ session('status') }}
                </div>
            @endif

            @forelse ($sites as $site)
                <div class="bg-white shadow-sm rounded-lg p-5 flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $site->subdomain }}</h3>
                            @if ($site->is_published)
                                <span class="inline-flex px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">Published</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700">Draft</span>
                            @endif
                            <span class="text-xs text-gray-500 uppercase tracking-wide">
                                {{ $site->source_type === 'html' ? 'Pasted HTML' : 'Template' }}
                            </span>
                        </div>
                        <div class="mt-1 text-sm text-gray-600">
                            <a href="{{ $site->publicUrl() }}" target="_blank" class="text-orange-600 hover:underline">
                                {{ $site->subdomain }}.{{ config('app.parent_domain') }} ↗
                            </a>
                            <span class="mx-2 text-gray-400">·</span>
                            <span>{{ $site->leads_count }} {{ Str::plural('lead', $site->leads_count) }}</span>
                            <span class="mx-2 text-gray-400">·</span>
                            <span>updated {{ $site->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('sites.show', $site) }}"
                           class="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50">Leads</a>
                        <a href="{{ route('sites.edit', $site) }}"
                           class="px-3 py-1.5 text-sm bg-gray-900 text-white rounded hover:bg-gray-800">Edit</a>
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-sm rounded-lg p-10 text-center">
                    <h3 class="text-lg font-semibold text-gray-900">No sites yet.</h3>
                    <p class="text-gray-600 mt-1">Create your first one-page site — it takes a minute.</p>
                    <a href="{{ route('sites.create') }}"
                       class="inline-flex items-center mt-4 px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-md">
                        + New site
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
