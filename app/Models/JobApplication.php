<?php

namespace App\Models;

use Database\Factories\JobApplicationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Status and emailed_at are deliberately not fillable. Status is set by
 * the application code, and emailed_at is set only after a notification is sent.
 *
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
#[Fillable([
    'job_listing_id',
    'user_id',
    'name',
    'email',
    'tel_num',
    'message',
])]
class JobApplication extends Model
{
    /** @use HasFactory<JobApplicationFactory> */
    use HasFactory;

    public const STATUS_NEW = 'new';

    public const STATUS_REVIEWED = 'reviewed';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_HIRED = 'hired';

    /**
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_NEW,
            self::STATUS_REVIEWED,
            self::STATUS_REJECTED,
            self::STATUS_HIRED,
        ];
    }

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
        return [
            'emailed_at' => 'datetime',
        ];
    }
}
