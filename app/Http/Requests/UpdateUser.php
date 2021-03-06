<?php

namespace App\Http\Requests;


class UpdateUser extends Request
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
            'name' => 'required|min:1|max:50',
            'password' => 'required|min:6|max:255',
            'email' => 'required|email|max:255',
            'age' => 'required|min:1|max:150',
            'city_id' => 'required|integer|exists:cities,id',
            'weight'  => 'required|integer|min:6|max:255'
        ];
    }
}
