<?php

namespace App\Providers;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Providers\stdClass;
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            $auth = $request->header('Authorization');
            if($auth == '') {
                return null;
            }
            $auth = explode(' ',$auth);
            if($auth[0] != 'Bearer'){
                return null;
            }
            if($token = $auth[1]){
                try {

                    $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
                    if ($decoded->exp < (time() + 21600)) { // 21600 detik = 6 jam
                        // Token kedaluwarsa, return null atau lakukan sesuai kebutuhan Anda
                        return ;
                    }
                    return User::find($decoded->uid);
                } catch(\Throwable $th) {
                    return null;
                }
            };


        });
    }
}
