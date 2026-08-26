<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChapterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:100',
            'chapter_no' => 'required|integer|min:1',
            'content' => 'required_without:oss_key|string',
            'oss_key' => 'required_without:content|string',
            'is_free' => 'required|in:0,1',
            'price' => 'nullable|numeric|min:0',
        ];
    }
}
