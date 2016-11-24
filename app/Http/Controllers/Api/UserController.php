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
use Sphinx\SphinxClient;
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
                'name' =>      'string|between:3,50',
                'start_age' =>  'integer|min:0|max:150',
                'end_age' =>    'integer|min:1|max:151',
                'start_weight' =>    'integer|min:1|max:351',
                'end_weight' =>    'integer|min:1|max:352',
                'sex' => 'integer|min:0|max:2',
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
        if (!empty($input['name']) || !empty($input['start_age']) || !empty($input['end_age']) || !empty($input['start_weight']) || !empty($input['end_weight']) || !empty($input['city'])) {
//            $conn = \DB::connection('sphinx');
//
//            //Делаем выборку с установкой разного веса полям
//            $results = $conn->select(\DB::raw("SELECT * FROM users WHERE MATCH (:name) OPTION  max_matches=50"), [
//                'name' => $name,
//            ]);
            $sphinx = new SphinxSearch();
            $query ='';
            //Поиск городу
            if(!empty($input['city'])){
                $query .= addslashes(strip_tags('@city '.$input['city'].' '));
            }

            if(!empty($input['name'])){
                //$sphinx->SetMatchMode( SphinxClient::SPH_MATCH_EXTENDED2  );
                $query .= addslashes(strip_tags('@name '.$input['name']));
            }

            if($query == ''){
                $sphinx->SetMatchMode(SphinxClient::SPH_MATCH_ALL);
            }
            $result = $sphinx->search($query, 'users')->limit($per_page+1, ((is_null($request->input('page')) || empty($request->input('page'))?1:$request->input('page'))-1)*$per_page);
            //Поиск по возрасту
            if(!empty($input['start_age']) || !empty($input['end_age'])){
                $result->range('age', empty($input['start_age'])?0:(int)$input['start_age'], empty($input['end_age'])?200:(int)$input['end_age']);
            }
            //Поиск по весу
            if(!empty($input['start_weight']) || !empty($input['end_weight'])){
                $result->range('weight', empty($input['start_weight'])?0:(int)$input['start_weight'], empty($input['end_weight'])?352:(int)$input['end_weight']);
            }
            //Поиск по полу
            if(!empty($input['sex'])){
                $result->filter('sex', $input['sex']);
            }

            $result = $result->get();
            if ($result && is_array($result['matches'])) {
                $ids = array_keys($result['matches']);
                $users = User::whereIn('id', $ids)->get();
            }
        } else{
            $users = User::get();
        }
        if(!isset($users))
            return response()->json(
                [
                    'success' => true,
                    'users' => null,
                ]);

        $users = UserProfileTransformer::transform( $users );
        //Пагинация
        $Paginator = New Paginator($users,$per_page, null, ['path' => Paginator::resolveCurrentPath()]);
        //Входные параметры передаем всем старницам для перхода
        $Paginator->appends($request->except(['page']));
        $page = $Paginator->toArray();
        return response()->json(
            [
                'success' => true,
                'users' => $page,
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

    /**
     * Get user info
     *
     * @param  Request  $request
     * @param integer $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(Request $request, $user_id)
    {
        $user = User::find($user_id);
        if(is_null($user)|| !$user->exists) {
            return response()->json(['success'=> false, 'error'=> ['common' => trans('validation.exists_user_id_db', ['id' => $user_id])], 'code' => 404], 404);
        }
        $user = UserProfileTransformer::transform($user);
        return response()->json(
            [
                'success' => true,
                'user' => $user,
            ]);
//
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param  integer $user_id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $user_id)
    {
        //
        $user = User::find($user_id);
        if(is_null($user)|| !$user->exists) {
            return response()->json(['success'=> false, 'error'=> ['common' => trans('validation.exists_user_id_db', ['id' => $user_id])], 'code' => 404], 404);
        }
        $user->delete();
        return response()->json(
            [
                'success' => true,
            ]);
    }



}
