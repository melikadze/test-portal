<?php

namespace App\Http\Requests;

use App\Models\Article;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('create', Article::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'sometimes|required',
            'body' => 'sometimes|required',
            'image' => 'sometimes|required|mimes:jpeg,jpg,png,gif|max:10240'
        ];
    }
}
