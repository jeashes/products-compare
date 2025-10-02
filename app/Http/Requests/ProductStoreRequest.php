<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    /**
     * @return array<string,string>
     */
    public function rules(): array
    {
        return [
            'category_id' => 'required|integer|exists:categories,id',
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'price' => 'required|numeric|min:0|max:999999.99',
            'rating' => 'required|numeric|min:0|max:5',
            'trending_order' => 'required|integer|min:0',
            'pros' => 'nullable|array|max:10',
            'pros.*' => 'string|max:255',
            'cons' => 'nullable|array|max:10',
            'cons.*' => 'string|max:255',
            'key_features' => 'nullable|array|max:15',
            'key_features.*' => 'string|max:255',
        ];
    }
}
