<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products')->ignore($this->route('product'))],
            'description' => ['nullable', 'string'],
            'price_grosze' => ['required', 'integer', 'min:0'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'stock_qty' => ['required', 'integer', 'min:0'],
            'is_physical' => ['boolean'],
            'is_published' => ['boolean'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'max:2048'],
            'filter_options' => ['nullable', 'array'],
            'filter_options.*' => ['integer', 'exists:product_filter_options,id'],
        ];
    }
}
