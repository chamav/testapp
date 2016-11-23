<?php

namespace App\Http\Controllers\Api;



use App\Http\Controllers\Controller;
use App\Services\CreateUserService;
use App\UserUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\CreateUser;
use Tymon\JWTAuth\Facades\JWTAuth;

use Hash;
use Carbon\Carbon;
use Log;
use Storage;
use File;
use Cache;

//use Storage;

class UserController extends Controller
{

    protected $request;

    /**
     * Display a listing of the resource.
     *
     * @param Pagination $request
     * @return \Illuminate\Http\Response
     */
    public function index(Pagination $request)
    {
        $input = $request->all();
        $user = $request->user();

        $validator = \Validator::make(
            $input,
            [
                'query' => 'string|between:3,50',
            ]
        );
        if ($validator->fails())
        {
            return response()->json(
                [
                    'success' => false, 'error'=> ['message' => $validator->messages(), 'code' => 406],
                ], 406);
        }



        return response()->json(
            [
                'success' => true,
            ]);

    }
    /**
     * Registration user.
     *
     * @param CreateUser  $request
     * @param CreateUserService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function registration(CreateUser $request, CreateUserService $service)
    {
        $input = $request->only(['name', 'email', 'password', 'lang', 'location', 'nickname']);
        $input['nickname'] = mb_strtolower($input['nickname']);
        $input['email']     = mb_strtolower($input['email']);

        $validator = Validator::make(
            $input,
            [
                'name' => 'nullable|min:1|max:50',
                'password' => 'required|min:6|max:255',
                'email' => 'required|email|max:255|unique:users',
            ]
        );
        if ($validator->fails())
        {
            return response()->json(
                [
                    'success' => false, 'error'=> ['message' => $validator->messages(), 'code' => 406],
                ], 406);
        }
        $user = $service->make($request);
        $token = JWTAuth::attempt(['email' => $request->get('email'), 'password' => $request->get('password')]);



        // send the token back to the client
        return response()->json(
            [
                'success' => true, 'token' => $token,
            ], 201)
            ->header('Authorization', 'Bearer '.$token)
            ->cookie('jsession', $token, 60, "/", config(['domain_name'], 'localhost'), true, false)
            ;
    }
    /**
     * Authorization of user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // grab credentials from the request
        $input = $request->only('email', 'password');
        ini_set('session.cookie_httponly', 0);
        $validator = Validator::make(
            $input,
            [
                'password' => 'required|min:6|max:255',
                'email' => 'required|email'
            ]
        );
        if ($validator->fails())
        {
            return response()->json(
                [
                    'success' => false, 'error'=> ['message' => $validator->messages(), 'code' => 406],
                ], 406);
        }
        $input['email'] = mb_strtolower($input['email']);
        if (!$token = JWTAuth::attempt($input)) {
            return response()->json(['success'=> false, 'error'=> ['common' => trans('validation.auth')]], 404);
        }

        return response()
            ->json(['success'=> true,'token' => $token])
            ->header('Authorization', 'Bearer '.$token)
            ;
    }

    public function get_user_details(Request $request)
    {
        $input = $request->all();
        $user = JWTAuth::toUser($input['token']);
        return response()->json(['result' => $user]);
    }



}
