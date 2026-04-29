<x-mail::message>
# New lead on **{{ $site->subdomain }}.{{ config('app.parent_domain') }}**

@if ($lead->name())
**Name:** {{ $lead->name() }}
@endif

@if ($lead->email())
**Email:** {{ $lead->email() }}
@endif

@if (! empty($lead->payload_json))
---

**Submitted fields:**

@foreach ($lead->payload_json as $key => $value)
- **{{ $key }}:** {{ is_array($value) ? json_encode($value) : $value }}
@endforeach
@endif

---

@if ($lead->source_form)
_Form: {{ $lead->source_form }}_
@endif

_Captured {{ $lead->created_at->diffForHumans() }} from {{ $lead->ip_address }}._

<x-mail::button :url="route('sites.show', $site)">
View all leads
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
