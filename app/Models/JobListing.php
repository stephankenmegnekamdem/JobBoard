<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['team_id', 'contact_id', 'title', 'short_description', 'description',
    'keywords', 'location', 'salary', 'working_time', 'link', 'image', 'published_at',
    'expires_at'])]
class JobListing extends Model
{
    use HasFactory;

    public function company(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'expires_at' => 'datetime'];
    }
}
