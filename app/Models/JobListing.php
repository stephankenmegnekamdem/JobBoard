<?php

namespace App\Models;

use Database\Factories\JobListingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $team_id
 * @property int|null $contact_id
 * @property string $title
 * @property string $short_description
 * @property string $description
 * @property string|null $keywords
 * @property string|null $location
 * @property string|null $salary
 * @property string|null $working_time
 * @property string|null $link
 * @property string|null $image
 * @property Carbon|null $published_at
 * @property Carbon|null $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Team|null $company
 * @property-read User|null $contact
 * @property-read Collection<int, JobApplication> $applications
 */
#[Fillable([
    'team_id',
    'contact_id',
    'title',
    'short_description',
    'description',
    'keywords',
    'location',
    'salary',
    'working_time',
    'link',
    'image',
    'published_at',
    'expires_at',
])]
class JobListing extends Model
{
    /** @use HasFactory<JobListingFactory> */
    use HasFactory;

    /**
     * The company that posted this listing.
     *
     * @return BelongsTo<Team, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    /**
     * The contact may be null if their account has been deleted.
     *
     * @return BelongsTo<User, $this>
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contact_id');
    }

    /**
     * @return HasMany<JobApplication, $this>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * Listings visible to the public: published, and not yet expired.
     *
     * @param  Builder<JobListing>  $query
     * @return Builder<JobListing>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(fn (Builder $query) => $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>', now()));
    }

    public function isOpen(): bool
    {
        return $this->published_at !== null
            && $this->published_at->lte(now())
            && ($this->expires_at === null || $this->expires_at->gt(now()));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
