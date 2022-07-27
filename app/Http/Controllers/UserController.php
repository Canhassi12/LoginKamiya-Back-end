<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;



class UserController extends Controller
{
    
    public function login(Request $request): Response
    {
        $credentials = $request->only('email', 'password');
        
        if (!Auth::attempt($credentials, true)) {
            return response()->json('email or password invalid', Response::HTTP_UNAUTHORIZED);
        }
        
        $response = [
            "message" => "login successful",
            "token" => auth()->user()->createToken('AuthToken')->plainTextToken,
        ];

        return response()->json($response, Response::HTTP_OK);
    }

    public function register(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:55|',
            'email' => 'required|unique:users',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag()->first();

            return response()->json($messages, Response::HTTP_BAD_REQUEST);
        }

        try {
            $inputs = $request->all();

            $user = [
                'name' => $inputs['name'],
                'email' => $inputs['email'],
                'password' => Hash::make($inputs['password']), 
            ];

            $user = User::create($user); 

            $token = $user->createToken('AuthToken')->plainTextToken;

            $response = [
                'message' => 'User has been added in database with sucessful',
                'user' => $user,
                'token' => $token
            ];

        } catch (Exception $e) {
            return response()->json($e, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        return response()->json($response, Response::HTTP_OK);
    }

    public function logout(): Response
    {
        auth()->user()->tokens()->delete();

        return response()->json('logout successfully', Response::HTTP_NO_CONTENT);
    }
}

