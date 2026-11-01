<?php

namespace App\Http\Requests\Teams;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Rules\UniqueTeamInvitation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CreateTeamInvitationRequest extends FormRequest
{
    /**
     * Only users who may invite members can submit this request.
     * Running this check here means unauthorized users get a 403
     * before any validation message is shown.
     */
    public function authorize(): bool
    {
        return Gate::allows('inviteMember', $this->team());
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', new UniqueTeamInvitation($this->team())],
            // Owner is excluded: ownership is only created by CreateTeam,
            // never granted through an invitation.
            'role' => ['required', 'string', Rule::in(array_column(TeamRole::assignable(), 'value'))],
        ];
    }

    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $isAdminInvite = $this->input('role') === TeamRole::Admin->value;

                if ($isAdminInvite && ! $this->user()->ownsTeam($this->team())) {
                    $validator->errors()->add('role', __('Only the team owner can invite admins.'));
                }
            },
        ];
    }

    private function team(): Team
    {
        $team = $this->route('team');

        abort_if(! $team instanceof Team, 404);

        return $team;
    }
}
