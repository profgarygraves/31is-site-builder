@php
    $presets = \App\Templates\Presets::all();
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create a new site</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Step 1: subdomain + email --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="font-semibold text-gray-900 mb-4">1. Pick your subdomain</h3>
                <form method="POST" action="{{ route('sites.store') }}" id="createForm" class="space-y-4">
                    @csrf
                    <input type="hidden" name="source_type" id="source_type" value="">
                    <input type="hidden" name="preset_key" id="preset_key" value="">
                    <input type="hidden" name="ai_brief" id="ai_brief" value="">

                    <div>
                        <label for="subdomain" class="block text-sm font-medium text-gray-700">Business name</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="text" name="subdomain" id="subdomain"
                                   value="{{ old('subdomain') }}"
                                   pattern="[a-z0-9][a-z0-9-]{1,30}[a-z0-9]"
                                   required autofocus
                                   class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-l-md border-gray-300 focus:ring-orange-500 focus:border-orange-500"
                                   placeholder="mybiz">
                            <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                .{{ config('app.parent_domain') }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Lowercase letters, numbers, dashes. 3–32 chars.</p>
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
                </form>
            </div>

            {{-- Step 2: choose how to start --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="font-semibold text-gray-900 mb-4">2. How do you want to start?</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach ($presets as $p)
                        <button type="button"
                                data-source="template"
                                data-preset="{{ $p['key'] }}"
                                class="text-left p-5 border border-gray-200 rounded-lg hover:border-orange-400 hover:shadow-md transition focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <div class="h-20 rounded-md mb-3 flex items-center justify-center text-3xl"
                                 style="background: {{ $p['gradient'] }};">
                                <span style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));">{{ $p['emoji'] }}</span>
                            </div>
                            <div class="font-semibold text-gray-900">{{ $p['title'] }}</div>
                            <div class="text-xs text-gray-600 mt-1">{{ $p['subtitle'] }}</div>
                        </button>
                    @endforeach

                    <button type="button"
                            data-source="ai"
                            class="text-left p-5 border border-gray-200 rounded-lg hover:border-orange-400 hover:shadow-md transition focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <div class="h-20 rounded-md mb-3 flex items-center justify-center text-3xl"
                             style="background: linear-gradient(135deg, #a855f7, #6b21a8);">
                            <span style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));">🤖</span>
                        </div>
                        <div class="font-semibold text-gray-900">AI fills it in</div>
                        <div class="text-xs text-gray-600 mt-1">Tell Claude what you're selling and it drafts the whole page for you to edit.</div>
                    </button>

                    <button type="button"
                            data-source="html"
                            class="text-left p-5 border border-gray-200 rounded-lg hover:border-orange-400 hover:shadow-md transition focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <div class="h-20 rounded-md mb-3 flex items-center justify-center text-3xl"
                             style="background: linear-gradient(135deg, #4b5563, #111827);">
                            <span style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));">📋</span>
                        </div>
                        <div class="font-semibold text-gray-900">Paste HTML</div>
                        <div class="text-xs text-gray-600 mt-1">Already have HTML from Claude, v0, Bolt, or anywhere else? Paste it in.</div>
                    </button>
                </div>
            </div>

            {{-- AI brief modal --}}
            <div id="aiModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50" id="aiBackdrop"></div>
                <div class="relative bg-white rounded-lg shadow-2xl max-w-lg w-full p-6">

                    {{-- Default state: brief input --}}
                    <div id="aiBriefState">
                        <h3 class="font-semibold text-lg mb-2">Tell Claude what you're selling</h3>
                        <p class="text-sm text-gray-600 mb-4">A sentence or two is enough. Claude will draft the title, copy, features, and FAQ — you can edit everything afterward.</p>
                        <textarea id="aiBriefInput" rows="5" class="w-full rounded-md border-gray-300 focus:ring-orange-500 focus:border-orange-500 text-sm"
                                  placeholder="A subscription box for small-batch hot sauces, focused on the daring chili-head looking for variety beyond what's on the grocery shelf."></textarea>
                        <div class="flex justify-end gap-2 mt-4">
                            <button type="button" id="aiCancel" class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Cancel</button>
                            <button type="button" id="aiSubmit" class="px-4 py-2 text-sm bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-md">Generate →</button>
                        </div>
                    </div>

                    {{-- Loading state: shown while Claude drafts --}}
                    <div id="aiLoadingState" class="hidden text-center py-8">
                        <div class="inline-flex items-center justify-center mb-4">
                            <svg class="animate-spin h-12 w-12 text-orange-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-lg mb-1">Claude is drafting your page</h3>
                        <p class="text-sm text-gray-600">Writing the hero, features, comparison, FAQ, and lead form.</p>
                        <p class="text-xs text-gray-500 mt-3" id="aiLoadingSubtext">This usually takes 15&ndash;30 seconds. Don't refresh.</p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const form = document.getElementById('createForm');
        const sourceField = document.getElementById('source_type');
        const presetField = document.getElementById('preset_key');
        const aiBriefField = document.getElementById('ai_brief');
        const aiModal = document.getElementById('aiModal');
        const aiBriefInput = document.getElementById('aiBriefInput');
        const aiBriefState = document.getElementById('aiBriefState');
        const aiLoadingState = document.getElementById('aiLoadingState');
        const aiLoadingSubtext = document.getElementById('aiLoadingSubtext');

        function isFormValid() {
            return form.reportValidity();
        }

        // Disable every path card so the user can't double-submit.
        function lockUI() {
            document.querySelectorAll('button[data-source]').forEach(b => {
                b.disabled = true;
                b.style.opacity = '0.5';
                b.style.cursor = 'not-allowed';
            });
        }

        // Switch the AI modal from brief-input to loading state and start a
        // friendly time counter so the user knows things are still happening.
        let aiTimerInterval = null;
        function showAiLoading() {
            aiBriefState.classList.add('hidden');
            aiLoadingState.classList.remove('hidden');
            const start = Date.now();
            aiTimerInterval = setInterval(() => {
                const secs = Math.floor((Date.now() - start) / 1000);
                if (secs < 30) {
                    aiLoadingSubtext.innerHTML = `Drafting (${secs}s) &mdash; usually takes 15&ndash;30 seconds.`;
                } else if (secs < 60) {
                    aiLoadingSubtext.innerHTML = `Drafting (${secs}s) &mdash; almost there, hang on.`;
                } else {
                    aiLoadingSubtext.innerHTML = `Drafting (${secs}s) &mdash; this is taking longer than usual. Still working.`;
                }
            }, 1000);
        }

        document.querySelectorAll('button[data-source]').forEach(btn => {
            btn.addEventListener('click', () => {
                const source = btn.dataset.source;
                if (!isFormValid()) return;

                if (source === 'template') {
                    sourceField.value = 'template';
                    presetField.value = btn.dataset.preset;
                    lockUI();
                    form.submit();
                } else if (source === 'html') {
                    sourceField.value = 'html';
                    lockUI();
                    form.submit();
                } else if (source === 'ai') {
                    aiModal.classList.remove('hidden');
                    aiModal.classList.add('flex');
                    aiBriefInput.focus();
                }
            });
        });

        document.getElementById('aiCancel').addEventListener('click', () => {
            aiModal.classList.add('hidden');
            aiModal.classList.remove('flex');
        });
        document.getElementById('aiBackdrop').addEventListener('click', () => {
            // Don't dismiss while loading — clicking outside should not cancel
            if (!aiLoadingState.classList.contains('hidden')) return;
            aiModal.classList.add('hidden');
            aiModal.classList.remove('flex');
        });
        document.getElementById('aiSubmit').addEventListener('click', () => {
            const brief = aiBriefInput.value.trim();
            if (brief.length < 10) { aiBriefInput.focus(); return; }
            sourceField.value = 'ai';
            aiBriefField.value = brief;
            lockUI();
            showAiLoading();
            form.submit();
        });
    })();
    </script>
</x-app-layout>
