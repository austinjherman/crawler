<?php

namespace App\Http\Controllers;

use App\User;
use Validator;
use App\Token;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Factories\TokenFactory;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Provide a token to user upon successful authentication
     *
     * @return void
     */
    public function login(Request $request)
    {
		$data = $request->json()->all();
        $rules = [
            'email' => 'required|email|exists:users',
            'password' => 'required',
        ];
        $validator = Validator::make($data, $rules);
        if($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $email = $request->json()->get('email');
        $inputPw = $request->json()->get('password');
        $user = User::where('email', $email)->first();
 
        if($user && Hash::check($inputPw, $user->password)) {
        	$tokenParts = TokenFactory::create([
        		'blacklist_in' => 2
        	]);
        	$token = new Token();
        	$token->token = Hash::make($tokenParts['apiKey']) . $user->hash;
            $token->active = true;
        	//$token->user_hash = Hash::make($user->hash);
        	$token->save();
        	return response()->json(['apiKey' => $tokenParts['apiKey'], 201);
    	}

    	return response()->json(['message' => 'username/password combination incorrect'], 400);

    }

}