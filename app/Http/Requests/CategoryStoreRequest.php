<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryStoreRequest extends FormRequest
{
    /**
     * @return array<string,array<int,string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255','unique:categories,name'],
            'slug' => ['required','string','max:255','unique:categories,slug'],
        ];
    }
}
