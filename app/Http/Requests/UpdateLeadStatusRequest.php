<?php

namespace App\Http\Requests;

use App\Enums\LeadStatus;
use App\Models\ClinicLead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $lead = $this->route('lead');

        return $lead instanceof ClinicLead && ($this->user()?->can('update', $lead) ?? false);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(LeadStatus::class)],
        ];
    }

    public function status(): LeadStatus
    {
        return $this->enum('status', LeadStatus::class) ?? LeadStatus::New;
    }
}
