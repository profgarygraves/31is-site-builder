<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use HasFactory;

    public const SOURCE_HTML = 'html';
    public const SOURCE_TEMPLATE = 'template';

    /** Subdomain slug rule — see also reserved_subdomains seeder. */
    public const SUBDOMAIN_REGEX = '/^[a-z0-9](?:[a-z0-9-]{0,30}[a-z0-9])?$/';

    protected $fillable = [
        'user_id',
        'subdomain',
        'source_type',
        'html_content_raw',
        'html_content',
        'template_id',
        'template_data',
        'notify_email',
        'thank_you_html',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'template_data' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /** Public URL — uses APP_DOMAIN to construct e.g. "gravesbros.31is.com". */
    public function publicUrl(): string
    {
        $scheme = config('app.url_scheme', 'https');
        $domain = config('app.parent_domain', '31is.test');

        return "{$scheme}://{$this->subdomain}.{$domain}";
    }
}
