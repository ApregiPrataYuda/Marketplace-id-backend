<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidationAddTagsRequest extends FormRequest
{
   /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'Nama produk wajib diisi.',
            'name.string'          => 'Nama produk harus berupa teks.',
            'name.max'             => 'Nama produk tidak boleh lebih dari 255 karakter.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'name'        => $this->has('name') ? trim($this->input('name')) : null,
        ]);
    }

}
