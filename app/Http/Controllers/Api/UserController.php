<?php

namespace App\Http\Controllers\Api;



use App\Http\Controllers\Controller;
use App\Http\Requests\Pagination;
use App\Services\CreateUserService;
use App\Transformers\UserProfileTransformer;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\CreateUser;
use sngrl\SphinxSearch\SphinxSearch;
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
        $per_page = $request->input('per_page') ? $request->input('per_page'): config('app.per_page',10);

        $results = [];
        if (!empty($input['query'])) {
//            $conn = \DB::connection('sphinx');
//
//            //Делаем выборку с установкой разного веса полям
//            $results = $conn->select(\DB::raw("SELECT * FROM users WHERE MATCH (:query) OPTION  max_matches=50"), [
//                'query' => $query,
//            ]);
            $sphinx = new SphinxSearch();
            $query = addslashes(strip_tags($input['query'] . '*'));
            $result = $sphinx->search($query, 'users')->limit($per_page+1, ((is_null($request->input('page')) || empty($request->input('page'))?1:$request->input('page'))-1)*$per_page)->get();
            if ($result && is_array($result['matches'])) {
                $ids = array_keys($result['matches']);
                $users = User::whereIn('id', $ids)->get();
            }
        } else{
            $users = User::get();
        }

        $users = UserProfileTransformer::transform( $users );


        return response()->json(
            [
                'success' => true,
                'users' => New Paginator($users,$per_page, null, ['path' => Paginator::resolveCurrentPath()]),
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

        $user = $service->make($request);
        $token = JWTAuth::attempt(['email' => $user->email, 'password' => $request->get('password')]);



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
