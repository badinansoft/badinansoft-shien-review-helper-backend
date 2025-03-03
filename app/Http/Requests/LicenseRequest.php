<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LicenseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'license_key' => 'required|string'
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
