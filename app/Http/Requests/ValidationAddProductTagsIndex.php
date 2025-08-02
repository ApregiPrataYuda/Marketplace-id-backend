<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class ValidationAddProductTagsIndex extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|exists:products,id',
            'tag_id' => 'required|integer|exists:tags,id',
          
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.integer'  => 'Produk tidak valid.',
            'product_id.exists'   => 'Produk yang dipilih tidak ditemukan.',

            'tag_id.required' => 'Tag wajib dipilih.',
            'tag_id.integer'  => 'Tag tidak valid.',
            'tag_id.exists'   => 'Tag yang dipilih tidak ditemukan.',
        ];
    }
}
