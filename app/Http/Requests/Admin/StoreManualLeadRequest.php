<?php

namespace App\Http\Requests\Admin;

use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManualLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Budget is free text ("300K", "$350,000", "Luxury", etc.) but staff may still
        // type/paste a plain number — normalize to string so validation doesn't reject it.
        if ($this->has('budget') && $this->input('budget') !== null) {
            $this->merge(['budget' => (string) $this->input('budget')]);
        }
    }

    public function rules(): array
    {
        return [
            // Only the lead's name is truly required — staff must be able to save a
            // lead from partial information (phone/email/city recommended, not enforced).
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
            'intent' => ['nullable', 'in:buyer,seller,investor,other'],
            'status' => ['nullable', 'string', Rule::in(Lead::statusList())],
            'property_address' => ['nullable', 'string', 'max:500'],
            'beds_baths' => ['nullable', 'string', 'max:100'],
            'budget' => ['nullable', 'string', 'max:100'],
            'dop' => ['nullable', 'date'],
            'asking_price' => ['nullable', 'numeric', 'min:0'],
            'financing_status' => ['nullable', 'string', 'max:100'],
            'credit_score' => ['nullable', 'string', 'max:50'],
            'working_with_realtor' => ['nullable', 'boolean'],
            'timeline' => ['nullable', 'string', 'max:255'],
            'dnc_disclaimer' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'rep_name' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:100'],
            'sent_to' => ['nullable', 'string', 'max:255'],
            'assignment' => ['nullable', 'string', 'max:255'],
            'reason_in_house' => ['nullable', 'string', 'max:1000'],
            'realtor_response' => ['nullable', 'string', 'max:1000'],
            'assigned_agent_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
