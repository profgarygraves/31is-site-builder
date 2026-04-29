<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Cap on subdomains per user. Returns null when unlimited.
     * Tune via `USER_SITE_LIMIT` in .env (0 or empty = unlimited).
     */
    public static function siteLimit(): ?int
    {
        $v = config('app.user_site_limit');
        if ($v === null || $v === '' || (int) $v <= 0) {
            return null;
        }
        return (int) $v;
    }

    public function hasReachedSiteLimit(): bool
    {
        $limit = self::siteLimit();
        return $limit !== null && $this->sites()->count() >= $limit;
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
