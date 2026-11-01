<?php

namespace App\Http\Requests;

use App\Models\JobApplication;
use App\Models\JobListing;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreJobApplicationRequest extends FormRequest
{
    /**
     * Unknown or closed listings return 404, so their existence is not revealed.
     */
    public function authorize(): bool
    {
        $this->job();

        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'tel_num' => ['nullable', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('email')) {
                    return;
                }

                $job = $this->job();
                $userId = $this->user()?->id;
                $email = $this->input('email');

                $alreadyApplied = JobApplication::query()
                    ->where('job_listing_id', $job->id)
                    ->where(function ($query) use ($userId, $email) {
                        $query->whereRaw('LOWER(email) = ?', [$email]);

                        if ($userId !== null) {
                            $query->orWhere('user_id', $userId);
                        }
                    })
                    ->exists();

                if ($alreadyApplied) {
                    $validator->errors()->add('email', __('You have already applied for this job.'));
                }
            },
        ];
    }

    /**
     * The controller must set user_id from the authenticated user,
     * never from the request, and must not accept status from the request.
     */
    public function applicantUserId(): ?int
    {
        return $this->user()?->id;
    }

    private function job(): JobListing
    {
        $job = $this->route('jobListing');

        abort_unless($job instanceof JobListing && $job->isOpen(), 404);

        return $job;
    }
}
