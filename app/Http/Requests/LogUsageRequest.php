<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LogUsageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'url' => 'required|string|max:255',
            'reviews_filled' => 'required|integer|min:0',
            'images_attached' => 'required|integer|min:0'
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
