<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    use HasFactory;

    public $timestamps = false; // we only have created_at, set on insert via DB default

    protected $fillable = [
        'site_id',
        'payload_json',
        'source_form',
        'ip_address',
        'user_agent',
        'referer',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'payload_json' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /** Best-effort name extraction from common form field names. */
    public function name(): ?string
    {
        $p = $this->payload_json ?? [];
        return $p['name'] ?? $p['full_name'] ?? $p['fullname'] ?? $p['your_name'] ?? null;
    }

    /** Best-effort email extraction. */
    public function email(): ?string
    {
        $p = $this->payload_json ?? [];
        return $p['email'] ?? $p['email_address'] ?? null;
    }
}
