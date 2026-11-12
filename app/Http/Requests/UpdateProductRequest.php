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
            'images.*' => ['image'],
            'image_order' => ['sometimes', 'array', 'size:'.count($this->route('product')->images ?? [])],
            'image_order.*' => ['required', 'integer', 'distinct', Rule::in(array_keys($this->route('product')->images ?? []))],
            'filter_options' => ['nullable', 'array'],
            'filter_options.*' => ['integer', 'exists:product_filter_options,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'image_order.size' => 'Kolejność musi obejmować wszystkie zdjęcia produktu. Odśwież stronę i spróbuj ponownie.',
            'image_order.*.in' => 'Wybrane zdjęcie nie należy do tego produktu. Odśwież stronę i spróbuj ponownie.',
            'image_order.*.distinct' => 'Każde zdjęcie może wystąpić w kolejności tylko raz.',
        ];
    }
}
