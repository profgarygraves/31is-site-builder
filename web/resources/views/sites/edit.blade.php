<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-3">
                {{ $site->subdomain }}.{{ config('app.parent_domain') }}
                @if ($site->is_published)
                    <span class="inline-flex px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">Published</span>
                @else
                    <span class="inline-flex px-2 py-0.5 text-xs rounded-full bg-amber-100 text-amber-800">Draft</span>
                @endif
            </h2>
            <div class="flex items-center gap-2">
                @if ($site->is_published)
                    <a href="{{ $site->publicUrl() }}" target="_blank"
                       class="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50">
                        View live ↗
                    </a>
                @else
                    <span title="Tick the Published checkbox below and Save before visiting"
                          class="px-3 py-1.5 text-sm border border-gray-200 text-gray-400 rounded cursor-not-allowed">
                        View live (publish first)
                    </span>
                @endif
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

            @unless ($site->is_published)
                <div class="bg-amber-50 border border-amber-200 text-amber-900 px-4 py-3 rounded mb-4 flex items-start gap-3">
                    <span class="text-xl leading-none">⚠️</span>
                    <div class="flex-1">
                        <div class="font-semibold">This site is in <em>Draft</em> mode</div>
                        <div class="text-sm mt-1">
                            Visitors to <code class="bg-white px-1 py-0.5 rounded text-xs">{{ $site->subdomain }}.{{ config('app.parent_domain') }}</code> will see a 404 until you check the <strong>Published</strong> box below and click <strong>Save</strong>.
                        </div>
                    </div>
                </div>
            @endunless

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
                    <label class="flex items-center gap-2 mt-6 px-3 py-2 rounded-md border {{ $site->is_published ? 'border-green-300 bg-green-50' : 'border-amber-300 bg-amber-50' }}">
                        <input type="checkbox" name="is_published" value="1" {{ $site->is_published ? 'checked' : '' }}
                               class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                        <span class="text-sm font-semibold text-gray-800">Published</span>
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
