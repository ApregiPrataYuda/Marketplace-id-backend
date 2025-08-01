<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidationAddVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|exists:products,id',
            'name'       => 'required|string|max:100', // sesuai schema kamu (VARCHAR 100)
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.integer'  => 'Produk tidak valid.',
            'product_id.exists'   => 'Produk yang dipilih tidak ditemukan.',

            'name.required'       => 'Nama varian wajib diisi.',
            'name.string'         => 'Nama varian harus berupa teks.',
            'name.max'            => 'Nama varian maksimal 100 karakter.',
        ];
    }
}
