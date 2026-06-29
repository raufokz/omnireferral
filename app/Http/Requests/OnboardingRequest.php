<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'brokerage_name' => ['nullable', 'string', 'max:255'],
            'broker_name' => ['nullable', 'string', 'max:255'],
            'office_email' => ['nullable', 'email', 'max:255'],
            'office_phone' => ['nullable', 'string', 'max:20'],
            'office_address' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'primary_area_of_service' => ['nullable', 'string', 'max:255'],
            'radius_miles' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'secondary_area' => ['nullable', 'string', 'max:255'],
            'lead_types' => ['nullable', 'string', 'max:500'],
            'languages' => ['nullable', 'string', 'max:255'],
            'how_did_you_hear_about_us' => ['nullable', 'string', 'max:255'],
            'representative_name' => ['nullable', 'string', 'max:255'],
            'e_signature' => ['nullable', 'string'],
            'upload_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Please enter your full name.',
            'phone.required' => 'A phone number is required.',
            'email.required' => 'An email address is required.',
            'email.email' => 'Enter a valid email address.',
            'city.required' => 'City is required.',
            'state.required' => 'State is required.',
            'postal_code.required' => 'Postal / ZIP code is required.',
            'upload_picture.image' => 'The headshot must be an image file (JPEG, PNG, GIF, or WebP).',
            'upload_picture.max' => 'The headshot must not exceed 5 MB.',
            'terms.accepted' => 'You must agree to the terms and conditions.',
        ];
    }
}
