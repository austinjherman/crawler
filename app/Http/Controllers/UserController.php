<?php

namespace App\Http\Controllers;

use App\User;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{   

    /**
     * Create a new user
     */
    public function create(Request $request) {
        $data = $request->json()->all();
        $rules = [
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required'
        ];
        $validator = Validator::make($data, $rules);
        if($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user = new User();
        $user->email = $request->json()->get('email');
        $user->password = Hash::make($request->json()->get('password'));
        $user->save();
        return response()->json($user, 201);
    }

    /**
     * Get User Details
     */
    public function read(Request $request) {
        
    }

    /**
     * Update a user
     */
    public function update(Request $request) {
        
    }

    /**
     * Delete a user
     */
    public function delete(Request $request) {
        
    }

}
