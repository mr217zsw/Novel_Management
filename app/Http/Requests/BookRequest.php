<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('book') ?? $this->route('id');

        return [
            'title' => 'required|string|max:100',
            'cover_url' => 'nullable|url',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'copyright_type' => 'required|in:1,2,3,4',
            'copyright_price' => 'nullable|numeric|min:0',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date',
            'royalty_rate' => 'nullable|numeric|min:0|max:100',
            'min_price' => 'nullable|numeric|min:0',
        ];
    }
}
