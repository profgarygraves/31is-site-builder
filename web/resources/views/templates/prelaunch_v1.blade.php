@php
    use App\Services\HtmlSanitizer;

    $brandName       = $data['brand_name'] ?? 'BRAND';
    $brand1          = $data['brand_color_1'] ?? '#ff6a1a';
    $brand2          = $data['brand_color_2'] ?? '#c4321b';
    $announce        = $data['announcement_text'] ?? 'LAUNCHING SOON · JOIN THE WAITLIST';

    $hero            = $data['hero'] ?? [];
    $heroImages      = array_values(array_filter($hero['images'] ?? []));
    $heroImage       = $heroImages[0] ?? 'https://picsum.photos/seed/'.$site->subdomain.'/900/900';
    $heroCategory    = $hero['breadcrumb_category'] ?? '';
    $heroVariant     = $hero['breadcrumb_variant'] ?? '';
    $heroTitle       = $hero['title'] ?? 'Your product title';
    $heroCta         = $hero['cta_text'] ?? 'Notify me';
    $heroSubline     = $hero['subline'] ?? '';
    $heroDesc        = $hero['description'] ?? '';
    $heroPills       = array_values(array_filter($hero['feature_pills'] ?? [], fn ($p) => ! empty($p['label']) || ! empty($p['icon'])));
    $heroAccordion   = array_values(array_filter($hero['accordion'] ?? [], fn ($a) => ! empty($a['title'])));

    $tagline         = $data['tagline'] ?? '';

    $cards           = array_values(array_filter($data['what_you_get'] ?? [], fn ($c) => ! empty($c['title']) || ! empty($c['body'])));

    $compRows        = array_values(array_filter($data['comparison']['rows'] ?? [], fn ($r) => ! empty($r['feature'])));
    $usLabel         = $data['comparison']['us_label'] ?: $brandName;
    $themLabel       = $data['comparison']['them_label'] ?? 'Typical alternative';

    $stat            = $data['stat'] ?? [];
    $faq             = array_values(array_filter($data['faq'] ?? [], fn ($q) => ! empty($q['question'])));

    $form            = $data['lead_form'] ?? [];
    $formTitle       = $form['modal_title'] ?? 'Join the waitlist';
    $formSubtitle    = $form['modal_subtitle'] ?? 'Be first to know.';
    $showName        = $form['show_name'] ?? true;
    $showPhone       = $form['show_phone'] ?? false;

    $token           = app(HtmlSanitizer::class)->siteToken($site);
    $leadAction      = '/__lead/'.$site->id;
@endphp
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="robots" content="noindex" />
<title>{{ $brandName }} — {{ $heroTitle }}</title>
<style>
  :root {
    --brand-1: {{ $brand1 }};
    --brand-2: {{ $brand2 }};
    --brand-text-on: #ffffff;
    --ink: #1a1410;
    --ink-soft: #5a4d44;
    --line: #e8e1d8;
    --bg: #fdfaf6;
    --bg-card: #ffffff;
    --bg-warm: #fbf3e8;
    --radius: 14px;
    --radius-sm: 8px;
    --max: 1200px;
    --shadow-sm: 0 1px 2px rgba(20,10,0,0.04), 0 2px 8px rgba(20,10,0,0.04);
  }
  * { box-sizing: border-box; }
  html, body { margin: 0; padding: 0; }
  body { font-family: -apple-system, BlinkMacSystemFont, "Inter", "Segoe UI", Roboto, sans-serif;
         font-size: 16px; color: var(--ink); background: var(--bg); line-height: 1.5; -webkit-font-smoothing: antialiased; }
  img { max-width: 100%; display: block; }
  a { color: inherit; }
  button { font: inherit; cursor: pointer; }

  .marquee { background: var(--ink); color: var(--brand-text-on); overflow: hidden; font-size: 12px; font-weight: 600; letter-spacing: 0.12em; padding: 8px 0; }
  .marquee__track { display: flex; gap: 40px; white-space: nowrap; animation: marquee 35s linear infinite; width: max-content; }
  .marquee__track span { padding: 0 12px; }
  @keyframes marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }

  .site-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 32px; max-width: var(--max); margin: 0 auto; }
  .brand { font-weight: 800; font-size: 20px; letter-spacing: 0.08em; text-decoration: none; color: var(--brand-2); }

  .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: none; border-radius: 999px; font-weight: 600; padding: 10px 20px; font-size: 14px; text-decoration: none; transition: transform 0.12s ease, box-shadow 0.12s ease; }
  .btn--cta { background: linear-gradient(135deg, var(--brand-1), var(--brand-2)); color: var(--brand-text-on); box-shadow: 0 6px 20px rgba(0,0,0,0.18); }
  .btn--cta:hover { transform: translateY(-1px); }
  .btn--lg { padding: 14px 28px; font-size: 16px; }

  .hero { display: grid; grid-template-columns: 1.05fr 1fr; gap: 56px; max-width: var(--max); margin: 24px auto 60px; padding: 0 32px; }
  .hero__gallery { display: flex; flex-direction: column; gap: 12px; }
  .hero__main { position: relative; border-radius: var(--radius); overflow: hidden; background: var(--bg-warm); aspect-ratio: 1 / 1; }
  .hero__main img { width: 100%; height: 100%; object-fit: cover; }
  .gallery-nav { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.92); border: none; width: 38px; height: 38px; border-radius: 50%; font-size: 22px; line-height: 1; color: var(--ink); box-shadow: var(--shadow-sm); }
  .gallery-nav--prev { left: 12px; }
  .gallery-nav--next { right: 12px; }
  .hero__thumbs { display: flex; gap: 10px; }
  .thumb { width: 88px; height: 88px; padding: 0; border: 2px solid transparent; background: var(--bg-warm); border-radius: var(--radius-sm); overflow: hidden; cursor: pointer; }
  .thumb img { width: 100%; height: 100%; object-fit: cover; }
  .thumb.is-active { border-color: var(--brand-1); }

  .hero__details { display: flex; flex-direction: column; gap: 18px; }
  .hero__breadcrumb { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
  .pill { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 0.08em; }
  .pill--cat { background: #fde9d9; color: var(--brand-2); }
  .pill--variant { background: #f1e4d3; color: var(--ink); }

  .hero__title { font-size: clamp(32px, 5vw, 56px); font-weight: 800; line-height: 1.05; letter-spacing: -0.01em; margin: 4px 0 8px; }
  .hero__subline { color: var(--ink-soft); font-size: 13px; margin: 4px 0 0; }

  .features-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin: 8px 0 4px; }
  .feature { display: flex; flex-direction: column; align-items: center; text-align: center; background: var(--bg-warm); border: 1px solid var(--line); border-radius: var(--radius-sm); padding: 12px 6px; gap: 6px; }
  .feature__icon { font-size: 22px; line-height: 1; }
  .feature__label { font-size: 10px; font-weight: 700; letter-spacing: 0.08em; color: var(--ink); }

  .hero__description { color: var(--ink-soft); font-size: 15px; line-height: 1.65; margin: 4px 0; }

  .accordion { border-top: 1px solid var(--line); }
  .accordion details { border-bottom: 1px solid var(--line); padding: 14px 0; }
  .accordion summary { list-style: none; cursor: pointer; font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
  .accordion summary::-webkit-details-marker { display: none; }
  .accordion summary::after { content: "+"; font-size: 22px; color: var(--ink-soft); }
  .accordion details[open] summary::after { content: "−"; }
  .accordion p { color: var(--ink-soft); margin: 10px 0 0; font-size: 14px; }

  .tagline { background: linear-gradient(135deg, var(--brand-1), var(--brand-2)); color: var(--brand-text-on); padding: 80px 32px 60px; text-align: center; }
  .tagline__text { font-size: clamp(28px, 4.6vw, 56px); font-weight: 800; line-height: 1.08; margin: 0 auto 50px; max-width: 1000px; letter-spacing: -0.01em; }

  .what-you-get { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; max-width: var(--max); margin: 70px auto; padding: 0 32px; }
  .card { background: var(--bg-card); border: 1px solid var(--line); border-radius: var(--radius); padding: 28px; box-shadow: var(--shadow-sm); }
  .card__icon { width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, var(--brand-1), var(--brand-2)); color: var(--brand-text-on); display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 16px; }
  .card__title { font-size: 18px; font-weight: 700; margin: 0 0 8px; }
  .card__body { color: var(--ink-soft); font-size: 14px; margin: 0; line-height: 1.6; }

  .compare { background: var(--bg-warm); padding: 70px 32px; }
  .compare__title { text-align: center; font-size: clamp(28px, 4vw, 40px); font-weight: 800; margin: 0 0 28px; }
  .compare__table { width: 100%; max-width: 900px; margin: 0 auto; border-collapse: separate; border-spacing: 0; background: var(--bg-card); border: 1px solid var(--line); border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow-sm); }
  .compare__table th, .compare__table td { padding: 16px 20px; text-align: left; font-size: 14px; border-bottom: 1px solid var(--line); }
  .compare__table tbody tr:last-child td { border-bottom: none; }
  .compare__table thead th { font-size: 12px; letter-spacing: 0.08em; background: var(--bg-warm); font-weight: 700; }
  .compare__table th.us, .compare__table td.us { color: var(--brand-2); font-weight: 700; }
  .compare__table th.them, .compare__table td.them { color: var(--ink-soft); }

  .stat { text-align: center; padding: 90px 32px; background: linear-gradient(180deg, var(--bg) 0%, var(--bg-warm) 100%); }
  .stat__number { font-size: clamp(72px, 12vw, 140px); font-weight: 800; background: linear-gradient(135deg, var(--brand-1), var(--brand-2)); -webkit-background-clip: text; background-clip: text; color: transparent; line-height: 1; letter-spacing: -0.02em; margin-bottom: 12px; }
  .stat__claim { max-width: 600px; margin: 0 auto; color: var(--ink-soft); font-size: 17px; }

  .faq { max-width: 800px; margin: 60px auto 80px; padding: 0 32px; }
  .faq__title { text-align: center; font-size: clamp(26px, 3.5vw, 36px); font-weight: 800; margin: 0 0 28px; }
  .faq__list details { background: var(--bg-card); border: 1px solid var(--line); border-radius: var(--radius-sm); margin-bottom: 10px; padding: 16px 20px; box-shadow: var(--shadow-sm); }
  .faq__list summary { list-style: none; cursor: pointer; font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
  .faq__list summary::-webkit-details-marker { display: none; }
  .faq__list summary::after { content: "+"; font-size: 22px; color: var(--brand-1); }
  .faq__list details[open] summary::after { content: "−"; }
  .faq__list p { color: var(--ink-soft); font-size: 14px; margin: 12px 0 0; line-height: 1.65; }

  .site-footer { background: linear-gradient(135deg, var(--brand-1), var(--brand-2)); color: var(--brand-text-on); padding: 28px 32px; }
  .site-footer__inner { max-width: var(--max); margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; font-size: 13px; }

  .modal { position: fixed; inset: 0; z-index: 50; display: flex; align-items: center; justify-content: center; padding: 20px; pointer-events: none; opacity: 0; transition: opacity 0.2s ease; }
  .modal[aria-hidden="false"] { pointer-events: auto; opacity: 1; }
  .modal__backdrop { position: absolute; inset: 0; background: rgba(20,10,0,0.55); backdrop-filter: blur(4px); }
  .modal__panel { position: relative; background: var(--bg-card); border-radius: var(--radius); padding: 36px 32px 28px; width: 100%; max-width: 440px; box-shadow: 0 30px 80px rgba(0,0,0,0.35); transform: translateY(8px); transition: transform 0.2s ease; }
  .modal[aria-hidden="false"] .modal__panel { transform: translateY(0); }
  .modal__close { position: absolute; top: 10px; right: 14px; background: transparent; border: none; font-size: 28px; color: var(--ink-soft); }
  .modal__panel h3 { margin: 0 0 6px; font-size: 22px; }
  .modal__sub { color: var(--ink-soft); margin: 0 0 20px; font-size: 14px; }

  .lead-form { display: flex; flex-direction: column; gap: 14px; }
  .lead-form label { display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600; }
  .lead-form input { border: 1px solid var(--line); border-radius: var(--radius-sm); padding: 11px 14px; font-size: 15px; font-family: inherit; }
  .lead-form input:focus { outline: 2px solid var(--brand-1); border-color: var(--brand-1); }

  @media (max-width: 900px) {
    .hero { grid-template-columns: 1fr; gap: 32px; }
    .what-you-get { grid-template-columns: 1fr; }
    .features-row { grid-template-columns: repeat(2, 1fr); }
    .site-footer__inner { flex-direction: column; text-align: center; }
  }
</style>
</head>
<body>

<div class="marquee" role="presentation" aria-hidden="true">
  <div class="marquee__track">
    @for ($i = 0; $i < 8; $i++)
      <span>★ {{ $announce }}</span>
    @endfor
  </div>
</div>

<header class="site-header">
  <a href="#" class="brand">{{ $brandName }}</a>
  <button class="btn btn--cta" data-open-lead>{{ $heroCta }} →</button>
</header>

<section class="hero">
  <div class="hero__gallery">
    <div class="hero__main">
      @if (count($heroImages) > 1)
        <button type="button" class="gallery-nav gallery-nav--prev" data-gallery-prev aria-label="Previous">‹</button>
        <button type="button" class="gallery-nav gallery-nav--next" data-gallery-next aria-label="Next">›</button>
      @endif
      <img id="heroImage" src="{{ $heroImage }}" alt="{{ $heroTitle }}" />
    </div>
    @if (count($heroImages) > 1)
      <div class="hero__thumbs">
        @foreach ($heroImages as $i => $img)
          <button type="button" class="thumb {{ $i === 0 ? 'is-active' : '' }}" data-img="{{ $img }}">
            <img src="{{ $img }}" alt="" />
          </button>
        @endforeach
      </div>
    @endif
  </div>

  <div class="hero__details">
    @if ($heroCategory || $heroVariant)
      <div class="hero__breadcrumb">
        @if ($heroCategory) <span class="pill pill--cat">{{ $heroCategory }}</span> @endif
        @if ($heroVariant) <span class="pill pill--variant">{{ $heroVariant }}</span> @endif
      </div>
    @endif

    <h1 class="hero__title">{{ $heroTitle }}</h1>

    <button class="btn btn--cta btn--lg" data-open-lead>{{ $heroCta }} →</button>

    @if ($heroSubline)
      <p class="hero__subline">{{ $heroSubline }} &nbsp;·&nbsp; <span>🔒 Secure checkout</span></p>
    @endif

    @if (count($heroPills))
      <div class="features-row">
        @foreach ($heroPills as $pill)
          <div class="feature">
            <div class="feature__icon">{{ $pill['icon'] ?? '✦' }}</div>
            <div class="feature__label">{{ $pill['label'] ?? '' }}</div>
          </div>
        @endforeach
      </div>
    @endif

    @if ($heroDesc)
      <p class="hero__description">{{ $heroDesc }}</p>
    @endif

    @if (count($heroAccordion))
      <div class="accordion">
        @foreach ($heroAccordion as $item)
          <details>
            <summary>{{ $item['title'] }}</summary>
            <p>{{ $item['body'] ?? '' }}</p>
          </details>
        @endforeach
      </div>
    @endif
  </div>
</section>

@if ($tagline)
  <section class="tagline">
    <h2 class="tagline__text">{{ $tagline }}</h2>
  </section>
@endif

@if (count($cards))
  <section class="what-you-get">
    @foreach ($cards as $c)
      <div class="card">
        <div class="card__icon">{{ $c['icon'] ?? '✦' }}</div>
        <h3 class="card__title">{{ $c['title'] ?? '' }}</h3>
        <p class="card__body">{{ $c['body'] ?? '' }}</p>
      </div>
    @endforeach
  </section>
@endif

@if (count($compRows))
  <section class="compare">
    <h2 class="compare__title">Why choose {{ $usLabel }}?</h2>
    <table class="compare__table">
      <thead>
        <tr>
          <th></th>
          <th class="us">{{ $usLabel }}</th>
          <th class="them">{{ $themLabel }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($compRows as $r)
          <tr>
            <td>{{ $r['feature'] }}</td>
            <td class="us">{{ $r['us'] ?? '' }}</td>
            <td class="them">{{ $r['them'] ?? '' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </section>
@endif

@if (! empty($stat['percent']))
  <section class="stat">
    <div class="stat__number">{{ $stat['percent'] }}{{ $stat['suffix'] ?? '%' }}</div>
    <p class="stat__claim">{{ $stat['claim'] ?? '' }}</p>
  </section>
@endif

@if (count($faq))
  <section class="faq">
    <h2 class="faq__title">Frequently asked questions</h2>
    <div class="faq__list">
      @foreach ($faq as $q)
        <details>
          <summary>{{ $q['question'] }}</summary>
          <p>{{ $q['answer'] ?? '' }}</p>
        </details>
      @endforeach
    </div>
  </section>
@endif

<footer class="site-footer">
  <div class="site-footer__inner">
    <span style="font-weight:800; letter-spacing:0.08em;">{{ $brandName }}</span>
    <nav style="display:flex; gap:22px;">
      @if (! empty($data['footer']['contact_email']))
        <a href="mailto:{{ $data['footer']['contact_email'] }}">Contact</a>
      @endif
      <a href="mailto:abuse@{{ config('app.parent_domain') }}">Report</a>
    </nav>
    <span style="opacity:0.85;">Made with <strong>{{ config('app.name') }}</strong></span>
  </div>
</footer>

<div class="modal" id="leadModal" aria-hidden="true">
  <div class="modal__backdrop" data-close-lead></div>
  <div class="modal__panel" role="dialog" aria-labelledby="leadTitle">
    <button class="modal__close" data-close-lead aria-label="Close">×</button>
    <h3 id="leadTitle">{{ $formTitle }}</h3>
    <p class="modal__sub">{{ $formSubtitle }}</p>
    <form class="lead-form" method="POST" action="{{ $leadAction }}">
      <input type="hidden" name="_token" value="{{ $token }}">
      <input type="hidden" name="_form_id" value="hero_modal">
      @if ($showName)
        <label>Your name <input type="text" name="name" required></label>
      @endif
      <label>Email <input type="email" name="email" required></label>
      @if ($showPhone)
        <label>Phone (optional) <input type="tel" name="phone"></label>
      @endif
      <button type="submit" class="btn btn--cta btn--lg">{{ $heroCta }} →</button>
      <p style="font-size:11px; color:var(--ink-soft); text-align:center; margin:4px 0 0;">By signing up you agree to our terms.</p>
    </form>
  </div>
</div>

<script>
(function(){
  // Thumbnail switching
  var imgs = {!! json_encode($heroImages) !!};
  var idx = 0;
  var heroImg = document.getElementById('heroImage');
  document.querySelectorAll('.thumb').forEach(function(t, i){
    t.addEventListener('click', function(){
      document.querySelectorAll('.thumb').forEach(function(x){ x.classList.remove('is-active'); });
      t.classList.add('is-active');
      heroImg.src = t.dataset.img;
      idx = i;
    });
  });
  function nav(d){
    if (!imgs.length) return;
    idx = (idx + d + imgs.length) % imgs.length;
    heroImg.src = imgs[idx];
    document.querySelectorAll('.thumb').forEach(function(x, i){ x.classList.toggle('is-active', i === idx); });
  }
  var p = document.querySelector('[data-gallery-prev]'); if (p) p.addEventListener('click', function(){ nav(-1); });
  var n = document.querySelector('[data-gallery-next]'); if (n) n.addEventListener('click', function(){ nav(1); });

  // Modal
  var modal = document.getElementById('leadModal');
  document.querySelectorAll('[data-open-lead]').forEach(function(b){ b.addEventListener('click', function(){ modal.setAttribute('aria-hidden','false'); }); });
  document.querySelectorAll('[data-close-lead]').forEach(function(b){ b.addEventListener('click', function(){ modal.setAttribute('aria-hidden','true'); }); });
  document.addEventListener('keydown', function(e){ if (e.key === 'Escape') modal.setAttribute('aria-hidden','true'); });
})();
</script>
</body>
</html>
