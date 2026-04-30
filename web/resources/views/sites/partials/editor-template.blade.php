{{-- Path B — fill in the prelaunch_v1 template fields --}}
@php
    $d = $site->template_data ?? [];
    $hero = $d['hero'] ?? [];
    $heroImages = $hero['images'] ?? [];
    $pills = $hero['feature_pills'] ?? [];
    $accordion = $hero['accordion'] ?? [];
    $cards = $d['what_you_get'] ?? [];
    $rows = $d['comparison']['rows'] ?? [];
    $faq = $d['faq'] ?? [];
    $stat = $d['stat'] ?? [];
    $form = $d['lead_form'] ?? [];

    $field = function ($key, $value, $label, $type = 'text', $extra = '') {
        $name = 'template_data['.$key.']';
        $id = 'tpl_'.str_replace(['[', ']', '.'], '_', $key);
        return [
            'name' => $name,
            'id' => $id,
            'label' => $label,
            'type' => $type,
            'value' => old($name, $value),
            'extra' => $extra,
        ];
    };
@endphp

<style>
    .tpl-section { background: white; padding: 1.25rem; border-radius: 0.5rem; box-shadow: 0 1px 2px rgba(0,0,0,0.04); margin-bottom: 1rem; }
    .tpl-section h3 { font-weight: 600; color: #111827; margin-bottom: 0.75rem; }
    .tpl-grid { display: grid; gap: 0.75rem; }
    .tpl-grid-2 { grid-template-columns: 1fr 1fr; }
    .tpl-grid-3 { grid-template-columns: 1fr 1fr 1fr; }
    .tpl-grid-4 { grid-template-columns: 1fr 1fr 1fr 1fr; }
    @media (max-width: 768px) { .tpl-grid-2, .tpl-grid-3, .tpl-grid-4 { grid-template-columns: 1fr; } }
    .tpl-input, .tpl-textarea {
        width: 100%; border: 1px solid #d1d5db; border-radius: 0.375rem;
        padding: 0.5rem 0.75rem; font-size: 0.875rem;
    }
    .tpl-input:focus, .tpl-textarea:focus { outline: 2px solid #f97316; border-color: #f97316; }
    .tpl-label { display: block; font-size: 0.75rem; font-weight: 600; color: #4b5563; margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.05em; }
    .tpl-row { padding: 0.75rem; background: #f9fafb; border-radius: 0.375rem; margin-bottom: 0.5rem; }
</style>

{{-- BRAND --}}
<div class="tpl-section">
    <h3>Brand & header</h3>
    <div class="tpl-grid tpl-grid-3">
        <label><span class="tpl-label">Brand wordmark</span>
            <input class="tpl-input" type="text" name="template_data[brand_name]" value="{{ $d['brand_name'] ?? '' }}" placeholder="SCORCH"></label>
        <label><span class="tpl-label">Primary color</span>
            <input class="tpl-input" type="color" name="template_data[brand_color_1]" value="{{ $d['brand_color_1'] ?? '#ff6a1a' }}"></label>
        <label><span class="tpl-label">Accent color</span>
            <input class="tpl-input" type="color" name="template_data[brand_color_2]" value="{{ $d['brand_color_2'] ?? '#c4321b' }}"></label>
    </div>
    <label class="block mt-3"><span class="tpl-label">Marquee text (looping at the top)</span>
        <input class="tpl-input" type="text" name="template_data[announcement_text]" value="{{ $d['announcement_text'] ?? '' }}" placeholder="LAUNCHING SOON · JOIN THE WAITLIST"></label>
</div>

{{-- HERO --}}
<div class="tpl-section">
    <h3>Hero — product</h3>
    <div class="tpl-grid tpl-grid-2">
        <label><span class="tpl-label">Category (small pill)</span>
            <input class="tpl-input" type="text" name="template_data[hero][breadcrumb_category]" value="{{ $hero['breadcrumb_category'] ?? '' }}" placeholder="HOT SAUCE"></label>
        <label><span class="tpl-label">Variant (small pill)</span>
            <input class="tpl-input" type="text" name="template_data[hero][breadcrumb_variant]" value="{{ $hero['breadcrumb_variant'] ?? '' }}" placeholder="4-PACK"></label>
    </div>
    <label class="block mt-3"><span class="tpl-label">Product title</span>
        <input class="tpl-input" type="text" name="template_data[hero][title]" value="{{ $hero['title'] ?? '' }}" placeholder="THE ADDICTIVE HEAT"></label>
    <div class="tpl-grid tpl-grid-2 mt-3">
        <label><span class="tpl-label">CTA button text</span>
            <input class="tpl-input" type="text" name="template_data[hero][cta_text]" value="{{ $hero['cta_text'] ?? 'Notify me' }}" placeholder="Notify me"></label>
        <label><span class="tpl-label">Sub-line (under CTA)</span>
            <input class="tpl-input" type="text" name="template_data[hero][subline]" value="{{ $hero['subline'] ?? '' }}" placeholder="4-pack, 5 fl oz each"></label>
    </div>
    {{-- Hero images: upload + URL list, both feed into hero.images --}}
    <div class="block mt-3">
        <span class="tpl-label">Product images</span>
        <p class="text-xs text-gray-500 mb-2">
            Up to 5 images. Recommended ~800×800px, under 500&nbsp;KB each.
            Got a big one? Compress at <a href="https://tinypng.com" target="_blank" class="text-orange-600 underline">tinypng.com</a> first.
        </p>

        {{-- Drop a small CSRF + endpoint config the JS picks up --}}
        <div id="image-uploader"
             data-csrf="{{ csrf_token() }}"
             data-upload-url="{{ route('sites.images.store', $site) }}"
             data-delete-url="{{ route('sites.images.destroy', ['site' => $site, 'filename' => '__NAME__']) }}">

            <div class="flex items-center gap-3 mb-3">
                <label class="inline-flex items-center px-3 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-md cursor-pointer">
                    + Upload an image
                    <input type="file" id="image-file-input" accept="image/jpeg,image/png,image/webp,image/gif" class="sr-only">
                </label>
                <span id="image-upload-status" class="text-xs text-gray-600"></span>
            </div>

            {{-- Live thumbnail grid (also serves as the source of truth for save) --}}
            <div id="image-thumb-grid" class="grid grid-cols-2 sm:grid-cols-5 gap-2 mb-3">
                @foreach ($heroImages as $url)
                    <div class="relative group" data-image-url="{{ $url }}">
                        <img src="{{ $url }}" alt="" class="w-full aspect-square object-cover rounded border border-gray-200">
                        <button type="button" data-remove-image
                                class="absolute -top-1.5 -right-1.5 bg-white border border-gray-300 rounded-full w-6 h-6 text-xs font-bold text-gray-700 hover:bg-red-50 hover:border-red-300 hover:text-red-700"
                                aria-label="Remove image">×</button>
                    </div>
                @endforeach
            </div>

            <p class="text-xs text-gray-500">Or paste image URLs (one per line) — useful for stock photos:</p>
            <textarea class="tpl-textarea mt-1" rows="3" name="template_data[hero][images_text]"
                      id="image-urls-textarea"
                      placeholder="https://example.com/image1.jpg
https://example.com/image2.jpg">{{ is_array($heroImages) ? implode("\n", $heroImages) : '' }}</textarea>
        </div>
    </div>
    <label class="block mt-3"><span class="tpl-label">Description paragraph</span>
        <textarea class="tpl-textarea" rows="3" name="template_data[hero][description]" placeholder="Tell people what your product does and why it matters.">{{ $hero['description'] ?? '' }}</textarea></label>

    <h4 class="font-semibold text-sm mt-4 mb-2">Feature pills (4 small icon+label badges)</h4>
    <div class="tpl-grid tpl-grid-4">
        @for ($i = 0; $i < 4; $i++)
            <div class="tpl-row">
                <input class="tpl-input mb-1" type="text" name="template_data[hero][feature_pills][{{ $i }}][icon]" value="{{ $pills[$i]['icon'] ?? '' }}" placeholder="🔥">
                <input class="tpl-input" type="text" name="template_data[hero][feature_pills][{{ $i }}][label]" value="{{ $pills[$i]['label'] ?? '' }}" placeholder="PURE FIRE">
            </div>
        @endfor
    </div>

    <h4 class="font-semibold text-sm mt-4 mb-2">Accordion (3 collapsible sections under the hero)</h4>
    @for ($i = 0; $i < 3; $i++)
        <div class="tpl-row">
            <input class="tpl-input mb-2" type="text" name="template_data[hero][accordion][{{ $i }}][title]" value="{{ $accordion[$i]['title'] ?? '' }}" placeholder="Ingredients">
            <textarea class="tpl-textarea" rows="2" name="template_data[hero][accordion][{{ $i }}][body]" placeholder="What's inside.">{{ $accordion[$i]['body'] ?? '' }}</textarea>
        </div>
    @endfor
</div>

{{-- TAGLINE --}}
<div class="tpl-section">
    <h3>Big tagline (orange gradient section)</h3>
    <textarea class="tpl-textarea" rows="2" name="template_data[tagline]" placeholder="Irresistible savory-sweet heat for daring culinary explorers seeking extreme intensity.">{{ $d['tagline'] ?? '' }}</textarea>
</div>

{{-- WHAT YOU GET --}}
<div class="tpl-section">
    <h3>What You Get — 3 feature cards</h3>
    <div class="tpl-grid tpl-grid-3">
        @for ($i = 0; $i < 3; $i++)
            <div class="tpl-row">
                <input class="tpl-input mb-2" type="text" name="template_data[what_you_get][{{ $i }}][icon]" value="{{ $cards[$i]['icon'] ?? '' }}" placeholder="🔥">
                <input class="tpl-input mb-2" type="text" name="template_data[what_you_get][{{ $i }}][title]" value="{{ $cards[$i]['title'] ?? '' }}" placeholder="Punishing Heat">
                <textarea class="tpl-textarea" rows="3" name="template_data[what_you_get][{{ $i }}][body]" placeholder="Describe this benefit...">{{ $cards[$i]['body'] ?? '' }}</textarea>
            </div>
        @endfor
    </div>
</div>

{{-- COMPARISON --}}
<div class="tpl-section">
    <h3>Why choose us — comparison table</h3>
    <div class="tpl-grid tpl-grid-2">
        <label><span class="tpl-label">Our column header</span>
            <input class="tpl-input" type="text" name="template_data[comparison][us_label]" value="{{ $d['comparison']['us_label'] ?? '' }}" placeholder="(defaults to your brand name)"></label>
        <label><span class="tpl-label">Competitor column header</span>
            <input class="tpl-input" type="text" name="template_data[comparison][them_label]" value="{{ $d['comparison']['them_label'] ?? 'Typical alternative' }}"></label>
    </div>
    <div class="mt-3">
        @for ($i = 0; $i < 5; $i++)
            <div class="tpl-row tpl-grid tpl-grid-3" style="grid-template-columns: 1fr 1fr 1fr;">
                <input class="tpl-input" type="text" name="template_data[comparison][rows][{{ $i }}][feature]" value="{{ $rows[$i]['feature'] ?? '' }}" placeholder="Feature">
                <input class="tpl-input" type="text" name="template_data[comparison][rows][{{ $i }}][us]" value="{{ $rows[$i]['us'] ?? '' }}" placeholder="Us">
                <input class="tpl-input" type="text" name="template_data[comparison][rows][{{ $i }}][them]" value="{{ $rows[$i]['them'] ?? '' }}" placeholder="Them">
            </div>
        @endfor
    </div>
</div>

{{-- STAT --}}
<div class="tpl-section">
    <h3>Stat highlight</h3>
    <div class="tpl-grid tpl-grid-2">
        <label><span class="tpl-label">Big number (e.g. 82)</span>
            <input class="tpl-input" type="text" name="template_data[stat][percent]" value="{{ $stat['percent'] ?? '' }}" placeholder="82"></label>
        <label><span class="tpl-label">Suffix (% or other)</span>
            <input class="tpl-input" type="text" name="template_data[stat][suffix]" value="{{ $stat['suffix'] ?? '%' }}" placeholder="%"></label>
    </div>
    <label class="block mt-3"><span class="tpl-label">Claim sentence</span>
        <textarea class="tpl-textarea" rows="2" name="template_data[stat][claim]" placeholder="of [audience] say they want [thing] more than [alternative].">{{ $stat['claim'] ?? '' }}</textarea></label>
</div>

{{-- FAQ --}}
<div class="tpl-section">
    <h3>FAQ</h3>
    @for ($i = 0; $i < 5; $i++)
        <div class="tpl-row">
            <input class="tpl-input mb-2" type="text" name="template_data[faq][{{ $i }}][question]" value="{{ $faq[$i]['question'] ?? '' }}" placeholder="When will it ship?">
            <textarea class="tpl-textarea" rows="2" name="template_data[faq][{{ $i }}][answer]" placeholder="Your answer.">{{ $faq[$i]['answer'] ?? '' }}</textarea>
        </div>
    @endfor
</div>

{{-- LEAD FORM --}}
<div class="tpl-section">
    <h3>Lead form</h3>
    <div class="tpl-grid tpl-grid-2">
        <label><span class="tpl-label">Modal title</span>
            <input class="tpl-input" type="text" name="template_data[lead_form][modal_title]" value="{{ $form['modal_title'] ?? 'Join the waitlist' }}"></label>
        <label><span class="tpl-label">Modal subtitle</span>
            <input class="tpl-input" type="text" name="template_data[lead_form][modal_subtitle]" value="{{ $form['modal_subtitle'] ?? '' }}" placeholder="Be first to know when we ship."></label>
    </div>
    <div class="flex gap-4 mt-3">
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="template_data[lead_form][show_name]" value="1" {{ ($form['show_name'] ?? true) ? 'checked' : '' }}>
            Ask for name
        </label>
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="template_data[lead_form][show_phone]" value="1" {{ ($form['show_phone'] ?? false) ? 'checked' : '' }}>
            Ask for phone (optional)
        </label>
    </div>
</div>

<script>
(function () {
    const root = document.getElementById('image-uploader');
    if (!root) return;
    const csrf = root.dataset.csrf;
    const uploadUrl = root.dataset.uploadUrl;
    const deleteUrlTpl = root.dataset.deleteUrl;
    const fileInput = document.getElementById('image-file-input');
    const grid = document.getElementById('image-thumb-grid');
    const textarea = document.getElementById('image-urls-textarea');
    const status = document.getElementById('image-upload-status');

    const MAX_IMAGES = 5;
    const SOFT_KB = 500;     // warn above this
    const HARD_BYTES = 4 * 1024 * 1024; // server's cap

    // Wire up existing remove buttons (rendered server-side)
    grid.querySelectorAll('[data-remove-image]').forEach(b => bindRemove(b));

    fileInput.addEventListener('change', async () => {
        const file = fileInput.files[0];
        if (!file) return;
        fileInput.value = '';   // allow re-selecting the same file

        // Count check
        const current = grid.querySelectorAll('[data-image-url]').length;
        if (current >= MAX_IMAGES) {
            alert(`Already at the ${MAX_IMAGES}-image limit. Remove one before adding another.`);
            return;
        }

        // Type check
        if (!/^image\/(jpeg|png|webp|gif)$/.test(file.type)) {
            alert('Use JPG, PNG, WebP, or GIF.');
            return;
        }

        // Size checks
        if (file.size > HARD_BYTES) {
            alert(`File is ${(file.size / 1024 / 1024).toFixed(1)} MB — too large. Max 4 MB. Compress at tinypng.com first.`);
            return;
        }
        const sizeKB = Math.round(file.size / 1024);
        if (sizeKB > SOFT_KB) {
            const ok = confirm(`File is ${sizeKB} KB — recommend compressing at tinypng.com first (target under ${SOFT_KB} KB).\n\nUpload anyway?`);
            if (!ok) return;
        }

        // Upload
        status.textContent = `Uploading ${file.name}...`;
        const fd = new FormData();
        fd.append('image', file);
        fd.append('_token', csrf);

        try {
            const res = await fetch(uploadUrl, {
                method: 'POST',
                body: fd,
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                credentials: 'same-origin',
            });
            if (!res.ok) {
                const text = await res.text();
                throw new Error(`HTTP ${res.status}: ${text.slice(0, 200)}`);
            }
            const json = await res.json();
            addThumb(json.url);
            status.textContent = `Uploaded (${(json.size_bytes / 1024).toFixed(0)} KB). Save the form to publish.`;
            setTimeout(() => { status.textContent = ''; }, 4000);
        } catch (e) {
            status.textContent = '';
            alert('Upload failed: ' + e.message);
        }
    });

    function addThumb(url) {
        const wrap = document.createElement('div');
        wrap.className = 'relative group';
        wrap.dataset.imageUrl = url;
        wrap.innerHTML = `
            <img src="${escapeHtml(url)}" alt="" class="w-full aspect-square object-cover rounded border border-gray-200">
            <button type="button" data-remove-image
                    class="absolute -top-1.5 -right-1.5 bg-white border border-gray-300 rounded-full w-6 h-6 text-xs font-bold text-gray-700 hover:bg-red-50 hover:border-red-300 hover:text-red-700"
                    aria-label="Remove image">×</button>
        `;
        grid.appendChild(wrap);
        bindRemove(wrap.querySelector('[data-remove-image]'));
        appendUrlToTextarea(url);
    }

    function bindRemove(btn) {
        btn.addEventListener('click', async () => {
            const wrap = btn.closest('[data-image-url]');
            if (!wrap) return;
            const url = wrap.dataset.imageUrl;

            // If it's an uploaded file (under /storage/sites/), delete from disk too
            const m = url.match(/\/storage\/sites\/\d+\/([A-Za-z0-9_.-]+)$/);
            if (m) {
                const filename = m[1];
                const delUrl = deleteUrlTpl.replace('__NAME__', encodeURIComponent(filename));
                try {
                    await fetch(delUrl, {
                        method: 'DELETE',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                        credentials: 'same-origin',
                    });
                } catch (e) { /* swallow — best-effort cleanup */ }
            }

            // Remove from grid + textarea regardless
            removeUrlFromTextarea(url);
            wrap.remove();
        });
    }

    function appendUrlToTextarea(url) {
        const cur = textarea.value.split(/\r?\n/).map(s => s.trim()).filter(Boolean);
        if (!cur.includes(url)) cur.push(url);
        textarea.value = cur.join('\n');
    }

    function removeUrlFromTextarea(url) {
        const cur = textarea.value.split(/\r?\n/).map(s => s.trim()).filter(Boolean);
        textarea.value = cur.filter(u => u !== url).join('\n');
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }
})();
</script>
