<?php

namespace App\Features\Post\Models;

use App\Features\Shared\Support\Like;
use App\Models\User;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    /** `user_id` tidak mass-assignable; selalu diisi lewat relasi $user->posts(). */
    protected $fillable = [
        'title', 'slug', 'category', 'content', 'cover_image', 'is_published', 'published_at',
    ];

    protected $appends = ['cover_url', 'excerpt'];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function newFactory(): PostFactory
    {
        return PostFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn () => $this->cover_image ? Storage::disk('public')->url($this->cover_image) : null);
    }

    protected function excerpt(): Attribute
    {
        return Attribute::get(function () {
            $plain = preg_replace('/[#*_>`\[\]!()-]+/', ' ', (string) $this->content);

            return Str::limit(trim((string) preg_replace('/\s+/', ' ', (string) $plain)), 160);
        });
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function scopeSearch(Builder $query, ?string $term): void
    {
        if (blank($term)) {
            return;
        }

        $like = Like::contains($term);

        $query->where(function (Builder $q) use ($like) {
            $q->whereRaw("LOWER(title) LIKE ? ESCAPE '\\'", [$like])
                ->orWhereRaw("LOWER(content) LIKE ? ESCAPE '\\'", [$like]);
        });
    }

    public function scopeCategory(Builder $query, ?string $category): void
    {
        if (filled($category)) {
            $query->where('category', $category);
        }
    }

    public function scopeStatus(Builder $query, ?string $status): void
    {
        if (in_array($status, ['published', 'draft'], true)) {
            $query->where('is_published', $status === 'published');
        }
    }

    /** @param array{q?: ?string, category?: ?string, status?: ?string} $filters */
    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->search($filters['q'] ?? null)
            ->category($filters['category'] ?? null)
            ->status($filters['status'] ?? null);
    }

    /** Kolom ringan untuk daftar: konten dipotong 400 karakter (cukup untuk excerpt). */
    public function scopeListing(Builder $query): void
    {
        $query->select([
            'id', 'user_id', 'title', 'slug', 'category', 'cover_image',
            'is_published', 'published_at', 'created_at', 'updated_at',
        ])->selectRaw('substr(content, 1, 400) as content');
    }
}
