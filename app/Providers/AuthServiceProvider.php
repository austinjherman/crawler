<?php

namespace App\Providers;

use App\User;
use App\Token;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Illuminate\Support\ServiceProvider;

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
            $signer = new Sha256();
            if ($token = $request->header('Bearer')) {
                try {
                    $token = (new Parser())->parse((string) $token);
                } catch (\Exception $e) {
                    return null;
                }
                if ($token->verify($signer, getenv('JWT_SECRET'))) {
                    if ($jti = Token::where('jti', $token->getHeader('jti'))->first()) {
                        $data = new ValidationData();
                        $data->setIssuer(url('/'));
                        $data->setId($jti->jti);
                        if($token->validate($data)) {
                            return User::where('hash', $token->getClaim('uid'))->first();
                        }
                    }
                }
            }
            return null;
        });
    }
}
