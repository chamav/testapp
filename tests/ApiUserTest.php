<?php
/**
 * Created by PhpStorm.
 * User: andrp
 * Date: 20.09.2016
 * Time: 10:47
 */

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiUserTest extends TestCase
{


    private $api = '/api/v1/user/';
    private $api_prefix = '/api/v3/';

    /**
     * Тест обновления профиля  пользователя
     * Проверяем данные в БД
     * Проверяем выдачу обновленного профиля
     *
     * @return array
     */
    function testUpdateProfile()
    {
        $faker = Faker\Factory::create();
        $password = str_random(10);
        $user = factory(App\User::class)->create([
            'password' =>  Hash::make($password),
        ]);
        $req['token']   =   JWTAuth::attempt(['email' => $user->email, 'password' => $password]);
        $req['user_id'] =   $user->user_id;


        $name       =   $faker->userName;
        $data = [
            "sex"           => 2,
            "name"          => $name,
            'email' => $user->email,
            'password' => bcrypt(str_random(10)),
            'age' => 15,
            'weight' => 112,
            'city_id' => 34593,
        ];

        $method = "user";
        $json = $this->json('PUT', $this->api, $data,
            ['Authorization'=> 'Bearer '.$req['token']])
            ->seeJson([
                'success' => true,
            ])
            ->seeJsonStructure([
                'success',
            ])->assertResponseStatus(201);
        $this->seeInDatabase($user->getTable(), [
            'email'         =>  $user->email,
            'sex'           =>  2,
            'name'          =>  $name,
        ]);

        return $req;

    }





}