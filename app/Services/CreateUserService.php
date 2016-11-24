<?php
namespace App\Services;
use App\AuthUser;
use App\Http\Requests\CreateUser;
use App\Http\Requests\Request;
use App\Transformers\UserShortTransformer;
use App\User;
use App\UserStats;
use Hash;
use Cache;
/**
 * Created by PhpStorm.
 * User: develop
 * Date: 22.07.2016
 * Time: 16:19
 */
class CreateUserService
{

    public function make(Request $request)
    {
        $input = $request->only(['name', 'email', 'password', 'age', 'weight', 'city_id', 'sex']);
        $input['email'] = mb_strtolower($input['email']);

        $input =array_merge($input,
            [
                'password' => \Illuminate\Support\Facades\Hash::make($input['password']),
            ]);
        $user = $request->user();
        if(is_null($user)|| !$user->exists) {
            $user = User::create($input);
        } else {
            $user->update($input);
        }
        return $user;
    }


}