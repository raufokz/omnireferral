<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Public endpoint (rate-limited via middleware); keep open.
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:30'],
            'intent' => ['required', 'in:buyer,seller,investor,other'],
            'zip_code' => [
                Rule::requiredIf(fn () => $this->input('intent') === 'buyer'),
                'nullable',
                'string',
                'max:10',
                'regex:/^\d{5}(?:-\d{4})?$/',
            ],
            'property_address' => [
                Rule::requiredIf(fn () => $this->input('intent') === 'seller'),
                'nullable',
                'string',
                'max:255',
            ],
            'property_type' => ['nullable', 'string', 'max:100'],
            'budget' => ['nullable', 'integer'],
            'asking_price' => ['nullable', 'integer'],
            'timeline' => ['nullable', 'string', 'max:100'],
            'financing_status' => ['nullable', 'string', 'max:100'],
            'contact_preference' => ['nullable', 'string', 'max:50'],
            'package_slug' => ['nullable', 'string', 'exists:packages,slug'],
            'property_image' => ['nullable', 'image', 'max:4096'],
            'preferences' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Oops, looks like you missed your email!',
            'name.required' => 'We need your name so we know who to help.',
            'property_image.image' => 'Please upload a valid property photo.',
            'zip_code.required' => 'Add the ZIP code where you want to buy so we can route the request.',
            'zip_code.regex' => 'Enter a valid ZIP code using 5 digits or ZIP+4 format.',
            'property_address.required' => 'Add the full property address so we can review the seller opportunity properly.',
        ];
    }
}

