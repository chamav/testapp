<?php

namespace App\Http\Requests;


class Pagination extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'per_page' => 'integer|min:1|max:'.config('app.max_per_page'),
            'page' => 'integer|min:1',
        ];
    }
}
