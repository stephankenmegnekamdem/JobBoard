<?php

namespace App\Models;

use Database\Factories\JobApplicationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $job_listing_id
 * @property int|null $user_id
 * @property string $name
 * @property string $email
 * @property string|null $tel_num
 * @property string $message
 * @property string $status
 * @property Carbon|null $emailed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read JobListing $jobListing
 * @property-read User|null $applicant
 */
#[Fillable(['job_listing_id', 'user_id', 'name', 'email', 'tel_num', 'message', 'status', 'emailed_at'])]
class JobApplication extends Model
{
    /** @use HasFactory<JobApplicationFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<JobListing, $this>
     */
    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class);
    }

    /**
     * The registered user who applied, if they were logged in.
     *
     * @return BelongsTo<User, $this>
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['emailed_at' => 'datetime'];
    }
}
