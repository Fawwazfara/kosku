<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kos extends Model
{
    /** @use HasFactory<\Database\Factories\KosFactory> */
    use HasFactory;

    public const TYPE_PUTRI = 'putri';

    public const TYPE_PUTRA = 'putra';

    public const TYPE_CAMPUR = 'campur';

    public const TYPES = ['putri', 'putra', 'campur'];

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_HIDDEN = 'hidden';

    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = ['pending', 'active', 'hidden', 'rejected'];

    public const MAX_PHOTOS = 8;

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'type',
        'description',
        'price_min',
        'price_max',
        'address',
        'district',
        'latitude',
        'longitude',
        'whatsapp',
        'total_rooms',
        'available_rooms',
        'rules',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_min' => 'integer',
            'price_max' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
            'total_rooms' => 'integer',
            'available_rooms' => 'integer',
            'wa_clicks' => 'integer',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(KosPhoto::class)->orderBy('sort_order');
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class, 'facility_kos');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function primaryPhoto(): ?KosPhoto
    {
        return $this->photos->firstWhere('is_primary', true) ?? $this->photos->first();
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereHas('owner', fn (Builder $q) => $q->where('is_verified', true)->where('is_active', true));
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('available_rooms', '>', 0);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $like = '%'.strtolower($term).'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->whereRaw('lower(name) like ?', [$like])
                ->orWhereRaw('lower(district) like ?', [$like])
                ->orWhereRaw('lower(address) like ?', [$like]);
        });
    }

    public function scopeType(Builder $query, ?string $type): Builder
    {
        return filled($type) && in_array($type, self::TYPES, true) ? $query->where('type', $type) : $query;
    }

    public function scopePriceBetween(Builder $query, mixed $min, mixed $max): Builder
    {
        return $query
            ->when(filled($min), fn (Builder $q) => $q->where('price_min', '>=', (int) $min))
            ->when(filled($max), fn (Builder $q) => $q->where('price_min', '<=', (int) $max));
    }

    public function scopeWithFacilities(Builder $query, mixed $facilityIds): Builder
    {
        if (blank($facilityIds)) {
            return $query;
        }

        $ids = is_array($facilityIds) ? $facilityIds : [$facilityIds];

        return $query->whereHas('facilities', fn (Builder $q) => $q->whereIn('facilities.id', $ids), '=', count($ids));
    }

    public function getIsFullAttribute(): bool
    {
        return $this->available_rooms <= 0;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_PENDING => 'Menunggu verifikasi',
            self::STATUS_HIDDEN => 'Disembunyikan',
            self::STATUS_REJECTED => 'Ditolak',
            default => $this->status,
        };
    }

    public function getRatingAvgAttribute(): ?float
    {
        return $this->attributes['rating_avg'] ?? null;
    }

    public function getReviewsCountAttribute(): int
    {
        return (int) ($this->attributes['reviews_count'] ?? 0);
    }
}
