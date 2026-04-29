<?php

namespace App\Services;

use App\Models\Site;
use Masterminds\HTML5;

/**
 * Sanitizes student-pasted HTML (Path A) and rewrites it.
 *
 * Strategy: pure DOM-walk via html5-php (no HTMLPurifier).
 *
 * Why no HTMLPurifier: its element whitelist is too restrictive for
 * AI-generated designs — it strips <style>, <form>, <input>, etc. by
 * default. Loosening the allowlist enough to keep designs intact ends
 * up requiring nearly every HTML element, at which point we may as
 * well do the (small) set of dangerous-content removals ourselves.
 *
 * What we do remove / rewrite:
 *   - <script> elements (the only thing that can execute arbitrary JS)
 *   - on* event-handler attributes (onclick, onload, onerror, etc.)
 *   - javascript: / data: / vbscript: schemes in href, src, action, formaction, etc.
 *   - <object>, <embed>, <applet> (legacy plugin vectors)
 *   - <meta http-equiv="refresh"> (redirect attacks)
 *   - <iframe> without a sandbox attribute (enforced)
 *   - <form> action — rewritten to /__lead/{site_id}
 *
 * What we keep:
 *   - <style> tags and inline style="..." attributes (designs need them)
 *   - all other HTML5 elements and attributes
 *   - data attributes
 */
class HtmlSanitizer
{
    /** Attributes whose values must not start with javascript: / data: / vbscript:. */
    private const URL_ATTRS = ['href', 'src', 'action', 'formaction', 'background', 'poster', 'cite', 'longdesc'];

    /** Schemes we strip from URL attributes. */
    private const DANGEROUS_SCHEMES = '/^\s*(javascript|vbscript|data|file):/i';

    /** Tag names (lowercase) we remove entirely. */
    private const REMOVE_TAGS = ['script', 'object', 'embed', 'applet', 'base'];

    public function __construct() {}

    /**
     * @return array{html: string, form_count: int}
     */
    public function process(string $rawHtml, Site $site): array
    {
        $html = $rawHtml;

        // Wrap fragments in a doctype/body so html5-php has a full document.
        $hasDoctype = (bool) preg_match('/<!doctype/i', $html);
        $hasHtml = (bool) preg_match('/<html[^>]*>/i', $html);
        if (! $hasDoctype || ! $hasHtml) {
            $html = "<!doctype html><html><head><meta charset=\"utf-8\"></head><body>{$html}</body></html>";
        }

        $html5 = new HTML5();
        $doc = $html5->loadHTML($html);

        // 1. Remove dangerous tags (script, object, embed, applet, base).
        $this->removeTagsByName($doc, self::REMOVE_TAGS);

        // 2. Remove <meta http-equiv="refresh">.
        foreach (iterator_to_array($doc->getElementsByTagName('meta')) as $meta) {
            if (strcasecmp((string) $meta->getAttribute('http-equiv'), 'refresh') === 0) {
                $meta->parentNode?->removeChild($meta);
            }
        }

        // 3. Walk every element. Strip event handlers; sanitize URL attrs.
        $this->scrubElements($doc);

        // 4. Sandbox iframes.
        foreach ($doc->getElementsByTagName('iframe') as $iframe) {
            if (! $iframe->hasAttribute('sandbox')) {
                $iframe->setAttribute('sandbox', 'allow-scripts allow-same-origin allow-forms');
            }
        }

        // 5. Rewrite <form> elements to capture leads.
        $formCount = 0;
        foreach ($doc->getElementsByTagName('form') as $form) {
            $formCount++;
            $idx = $formCount;

            $form->setAttribute('method', 'POST');
            $form->setAttribute('action', "/__lead/{$site->id}");
            $form->removeAttribute('target');
            $form->setAttribute('enctype', 'application/x-www-form-urlencoded');

            $token = $doc->createElement('input');
            $token->setAttribute('type', 'hidden');
            $token->setAttribute('name', '_token');
            $token->setAttribute('value', $this->siteToken($site));
            $form->insertBefore($token, $form->firstChild);

            $formId = $doc->createElement('input');
            $formId->setAttribute('type', 'hidden');
            $formId->setAttribute('name', '_form_id');
            $formId->setAttribute('value', "form_{$idx}");
            $form->insertBefore($formId, $form->firstChild);
        }

        // 6. Inject the dynamic-form shim before </body>.
        $body = $doc->getElementsByTagName('body')->item(0);
        if ($body) {
            $script = $doc->createElement('script');
            $script->setAttribute('data-31is-shim', '1');
            $script->appendChild($doc->createTextNode($this->shimJs($site)));
            $body->appendChild($script);
        }

        return [
            'html' => $html5->saveHTML($doc),
            'form_count' => $formCount,
        ];
    }

    /** Stable per-site token used to validate form posts. */
    public function siteToken(Site $site): string
    {
        return hash_hmac('sha256', "site:{$site->id}", config('app.key'));
    }

    /** Remove every element whose lowercased tag is in $names. */
    private function removeTagsByName(\DOMDocument $doc, array $names): void
    {
        foreach ($names as $name) {
            $nodes = iterator_to_array($doc->getElementsByTagName($name));
            foreach ($nodes as $node) {
                $node->parentNode?->removeChild($node);
            }
        }
    }

    /** Walk every element; strip event handlers and dangerous URL schemes. */
    private function scrubElements(\DOMDocument $doc): void
    {
        $xpath = new \DOMXPath($doc);
        $elements = iterator_to_array($xpath->query('//*'));

        foreach ($elements as $el) {
            // Strip event-handler attributes (onclick, onload, onerror, ...).
            // Iterate over a copy because we mutate while iterating.
            $attrs = iterator_to_array($el->attributes ?? []);
            foreach ($attrs as $attr) {
                $name = strtolower($attr->nodeName);
                if (str_starts_with($name, 'on')) {
                    $el->removeAttribute($attr->nodeName);
                    continue;
                }
                if (in_array($name, self::URL_ATTRS, true)) {
                    $value = (string) $attr->nodeValue;
                    if (preg_match(self::DANGEROUS_SCHEMES, $value)) {
                        $el->removeAttribute($attr->nodeName);
                    }
                }
            }
        }
    }

    /** Visitor-side JS shim that catches dynamically-added forms. */
    private function shimJs(Site $site): string
    {
        $action = "/__lead/{$site->id}";
        $token = $this->siteToken($site);
        $tokenJs = json_encode($token, JSON_UNESCAPED_SLASHES);
        $actionJs = json_encode($action, JSON_UNESCAPED_SLASHES);
        return <<<JS
(function(){
  var token = {$tokenJs};
  var action = {$actionJs};
  document.addEventListener('submit', function(e){
    var f = e.target;
    if (!(f && f.tagName === 'FORM')) return;
    if (f.action && f.action.indexOf('/__lead/') !== -1) return;
    e.preventDefault();
    var fd = new FormData(f);
    fd.append('_token', token);
    fd.append('_form_id', 'dynamic');
    fetch(action, { method: 'POST', body: fd, credentials: 'omit' })
      .then(function(r){ if (r.redirected) location.href = r.url; else location.href = '/__thanks'; })
      .catch(function(){ location.href = '/__thanks'; });
  }, true);
})();
JS;
    }
}
