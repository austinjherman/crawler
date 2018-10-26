<?php

namespace App\Factories;

use App\Token;

class JtiFactory {

	static public function create() {
		do {
            $jti = base64_encode(str_random(20));
        } while (Token::where('jti', $jti)->first());
        $token = new Token();
        $token->jti = $jti;
        $token->save();
        return $jti;
	} 

}