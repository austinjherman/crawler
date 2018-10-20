<?php

namespace App\Factories;

use App\Token;
use Illuminate\Support\Facades\Hash;

class TokenFactory
{
    
    /**
     * Generate an API Key with embedded expiry
     */
    static public function create($args = []) {
        do {
            if(!empty($args && isset($args['blacklist_in']))) {
                $apiKey = base64_encode(str_random(40));
            }
            else {
                $apiKey = base64_encode(str_random(40));
            }
            $tokenHash = base64_encode(str_random(40));
        }
        while(Token::where('token', $apiKey)->first() && Token::where('token_hash', $tokenHash)->first());
        return ['tokenHash' => $tokenHash, 'apiKey' => $apiKey];
    }



}
