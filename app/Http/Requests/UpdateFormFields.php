<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormFields extends FormRequest
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
            'form_fields' => 'required|array',
            'form_fields.*' => 'required',
            'form_fields.*.field_name' => 'required',
            'form_fields.*.field_value' => 'nullable',
            'form_fields.*.type' => 'required',
            'form_fields.*.field_type' => 'nullable',
            'form_fields.*.meta1' => 'nullable',
            'form_fields.*.meta2' => 'nullable',
        ];
    }
}
