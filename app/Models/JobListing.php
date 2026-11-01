<?php

namespace App\Models;

use Database\Factories\JobListingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $team_id
 * @property int $contact_id
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
 * @property-read Team $company
 * @property-read User $contact
 * @property-read Collection<int, JobApplication> $applications
 */
#[Fillable(['team_id', 'contact_id', 'title', 'short_description', 'description', 'keywords', 'location', 'salary', 'working_time', 'link', 'image', 'published_at', 'expires_at'])]
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
     * The person in charge of this listing.
     *
     * @return BelongsTo<User, $this>
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<JobApplication, $this>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'expires_at' => 'datetime'];
    }
}
