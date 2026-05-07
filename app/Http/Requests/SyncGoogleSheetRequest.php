<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncGoogleSheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route group restricts to admin/staff; keep defensive check here.
        return (bool) $this->user()?->isStaff();
    }

    public function rules(): array
    {
        return [
            'sheet_url' => ['nullable', 'string', 'max:2000'],
            'sheet_csv_url' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

