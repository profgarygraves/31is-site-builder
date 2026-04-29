{{-- Path A — paste HTML from an AI builder --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="bg-white shadow-sm rounded-lg p-4">
        <div class="flex items-center justify-between mb-2">
            <label for="html_content_raw" class="block text-sm font-semibold text-gray-700">Paste your HTML</label>
            <span class="text-xs text-gray-500">Max 500 KB</span>
        </div>
        <textarea name="html_content_raw" id="html_content_raw" rows="24"
                  class="block w-full font-mono text-xs rounded-md border-gray-300 focus:ring-orange-500 focus:border-orange-500"
                  placeholder="<!doctype html>&#10;<html>&#10;...">{{ old('html_content_raw', $site->html_content_raw) }}</textarea>
        <p class="mt-2 text-xs text-gray-500">
            Any forms in your HTML will be rewired to capture leads to your dashboard automatically.
            Scripts will be stripped for safety, but inline styles and design are preserved.
        </p>
    </div>
    <div class="bg-white shadow-sm rounded-lg p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-gray-700">Preview</span>
            <button type="button" id="refreshPreview"
                    class="text-xs text-orange-600 hover:underline">Refresh ↻</button>
        </div>
        <iframe id="preview" sandbox="allow-same-origin"
                class="w-full h-[600px] border border-gray-200 rounded"
                srcdoc="{{ $site->html_content_raw ?? '' }}"></iframe>
    </div>
</div>

<script>
(function () {
    const textarea = document.getElementById('html_content_raw');
    const iframe = document.getElementById('preview');
    const refresh = document.getElementById('refreshPreview');
    const update = () => { iframe.srcdoc = textarea.value; };
    refresh.addEventListener('click', update);
    let t;
    textarea.addEventListener('input', () => { clearTimeout(t); t = setTimeout(update, 600); });
})();
</script>
