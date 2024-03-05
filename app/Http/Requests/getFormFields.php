<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class getFormFields extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'website_url' => 'required|url',
            'component_unique_id' => 'required',
        ];
    }
}
