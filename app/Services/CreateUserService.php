<?php
namespace App\Services;
use App\AuthUser;
use App\Http\Requests\CreateUser;
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

    public function make(CreateUser $request, $mergedAfter = null)
    {
        $input = $request->only(['name', 'email', 'password',]);
        $input['email'] = mb_strtolower($input['email']);

        $input =array_merge($input,
            [
                'password' => \Illuminate\Support\Facades\Hash::make($input['password']),
            ]);

        $user = User::create($input);
        return $user;
    }


}