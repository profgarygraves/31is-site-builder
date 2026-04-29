<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create a new site</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('sites.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="subdomain" class="block text-sm font-medium text-gray-700">Business name (subdomain)</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="text" name="subdomain" id="subdomain"
                                   value="{{ old('subdomain') }}"
                                   pattern="[a-z0-9][a-z0-9-]{1,30}[a-z0-9]"
                                   required
                                   class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-l-md border-gray-300 focus:ring-orange-500 focus:border-orange-500">
                            <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                .{{ config('app.parent_domain') }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Lowercase letters, numbers, and dashes. 3–32 chars.</p>
                        @error('subdomain') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="notify_email" class="block text-sm font-medium text-gray-700">Send leads to</label>
                        <input type="email" name="notify_email" id="notify_email"
                               value="{{ old('notify_email', auth()->user()->email) }}"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 focus:ring-orange-500 focus:border-orange-500">
                        @error('notify_email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <fieldset>
                        <legend class="block text-sm font-medium text-gray-700">How do you want to build it?</legend>
                        <div class="mt-2 space-y-3">
                            <label class="relative block bg-white border rounded-lg p-4 cursor-pointer focus-within:ring-2 focus-within:ring-orange-500 hover:border-orange-400">
                                <input type="radio" name="source_type" value="template" class="sr-only" {{ old('source_type', 'template') === 'template' ? 'checked' : '' }}>
                                <div class="flex items-start gap-3">
                                    <span class="text-2xl">🎨</span>
                                    <div>
                                        <span class="block text-sm font-semibold text-gray-900">Use a template</span>
                                        <span class="block text-xs text-gray-600 mt-0.5">Fill in the blanks on a polished pre-launch landing page. Fastest path.</span>
                                    </div>
                                </div>
                            </label>
                            <label class="relative block bg-white border rounded-lg p-4 cursor-pointer focus-within:ring-2 focus-within:ring-orange-500 hover:border-orange-400">
                                <input type="radio" name="source_type" value="html" class="sr-only" {{ old('source_type') === 'html' ? 'checked' : '' }}>
                                <div class="flex items-start gap-3">
                                    <span class="text-2xl">📋</span>
                                    <div>
                                        <span class="block text-sm font-semibold text-gray-900">Paste HTML from an AI builder</span>
                                        <span class="block text-xs text-gray-600 mt-0.5">Paste anything Claude/v0/Bolt generated. We'll sanitize it and capture leads from any forms.</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                        @error('source_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </fieldset>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('sites.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-md shadow-sm">
                            Create site →
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Highlight the selected radio card.
        document.querySelectorAll('input[name="source_type"]').forEach(r => {
            const update = () => {
                document.querySelectorAll('input[name="source_type"]').forEach(x => {
                    const card = x.closest('label');
                    card.classList.toggle('border-orange-500', x.checked);
                    card.classList.toggle('bg-orange-50', x.checked);
                });
            };
            r.addEventListener('change', update);
            update();
        });
    </script>
</x-app-layout>
