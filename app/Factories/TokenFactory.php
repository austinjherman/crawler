<?php

namespace App\Factories;

use App\User;
use App\Factories\JtiFactory;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class TokenFactory
{
    
    /**
     * Generate an API Key
     */
    static public function create(User $user) {
        $signer = new Sha256();
        $token = (new Builder())->setIssuer(url('/')) // Configures the issuer (iss claim)
                        //->setAudience(App::make('url')->to('/')) // Configures the audience (aud claim)
                        ->setId(JtiFactory::create(), true) // Configures the id (jti claim), replicating as a header item
                        ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
                        //->setNotBefore(time() + 60) // Configures the time that the token can be used (nbf claim)
                        ->setExpiration(time() + 3600*24) // Configures the expiration time of the token (exp claim)
                        ->set('uid', $user->hash) // Configures a new claim, called "uid"
                        ->sign($signer, getenv('JWT_SECRET')) // creates a signature using "testing" as key
                        ->getToken(); // Retrieves the generated token
        return $token;
    }

}
