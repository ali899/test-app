<?php

namespace App\Http\Controllers\Api;



use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{



    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'name' => 'required',
                'username' => 'required|unique:users',
                'password' => 'required',
                'passwordConfirm' => 'required|same:password',
            ]);
        if ($validator->fails()) {
            return false;
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $user['token'] = $user->createToken(env("APP_NAME"))->accessToken;
        return JsonResponse::respondSuccess(trans(JsonResponse::MSG_SUCCESS), $user);
    }


    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'username' => 'required',
                    'password' => 'required',
                ]);
            if ($validator->fails()) {
                return JsonResponse::respondError($validator->errors());
            }
            $user = User::where('username', $request->username)->first();

            if ($user) {
                if (Hash::check($request->password, $user->password)) {
                    $user['token'] = $user->createToken(env("APP_NAME"))->accessToken;
                    return JsonResponse::respondSuccess(trans(JsonResponse::MSG_SUCCESS), $user);
                } else {
                    return JsonResponse::respondError("login failed");
                }
            } else {
                return JsonResponse::respondError("user not found");
            }
        } catch (\Exception $ex) {
            return json_encode($ex->getMessage());
        }


    }

    public function getUser()
    {
        $user = Auth::guard('api')->user();

        return json_encode($user);
    }

    public function logout()
    {
        if (Auth::check()) {
            Auth::guard()->user()->token()->revoke();
            return response()->json(['success' => 'logout success'], 200);
        } else {
            return response()->json(['error' => 'something went wrong'], 500);
        }
    }
}
