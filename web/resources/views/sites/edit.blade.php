<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $site->subdomain }}.{{ config('app.parent_domain') }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ $site->publicUrl() }}" target="_blank"
                   class="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50">
                    View live ↗
                </a>
                <a href="{{ route('sites.show', $site) }}"
                   class="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50">
                    Leads
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-2 rounded mb-4">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('sites.update', $site) }}" id="siteForm">
                @csrf
                @method('PUT')

                <div class="bg-white shadow-sm rounded-lg p-5 mb-4 flex flex-wrap items-center gap-4">
                    <div class="flex-1 min-w-[260px]">
                        <label for="notify_email" class="block text-sm font-medium text-gray-700">Send leads to</label>
                        <input type="email" name="notify_email" id="notify_email"
                               value="{{ old('notify_email', $site->notify_email) }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 focus:ring-orange-500 focus:border-orange-500">
                    </div>
                    <label class="flex items-center gap-2 mt-6">
                        <input type="checkbox" name="is_published" value="1" {{ $site->is_published ? 'checked' : '' }}
                               class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                        <span class="text-sm font-medium text-gray-700">Published</span>
                    </label>
                    <button type="submit"
                            class="ml-auto px-5 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-md shadow-sm">
                        Save
                    </button>
                </div>

                @if ($site->source_type === 'html')
                    @include('sites.partials.editor-html', ['site' => $site])
                @else
                    @include('sites.partials.editor-template', ['site' => $site])
                @endif
            </form>
        </div>
    </div>
</x-app-layout>
