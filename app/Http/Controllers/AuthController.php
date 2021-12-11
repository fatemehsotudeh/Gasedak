<?php

namespace App\Http\Controllers;


use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Contracts\Providers\Auth;

class AuthController extends Controller
{
    //
    public function register(Request $request)
    {
        $phoneNumber = $request->phoneNumber;
        $password = $request->password;
        $invationalCode=$request->invationalCode;


        // Check if field is empty
        if (empty($phoneNumber) or empty($password)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }

        //check phoneNumber
        if(!preg_match("/^[0-9]{11}$/", $phoneNumber)) {
            return response()->json(['status' => 'error', 'message' => 'You must provide the correct phoneNumber']);
        }

        // Check if password is less than 6 character
        if (strlen($password) < 6) {
            return response()->json(['status' => 'error', 'message' => 'Password should be min 6 character']);
        }

        // Check if user already exist
        if (User::where('phoneNumber', '=', $phoneNumber)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'User already exists with this phoneNumber'],422);
        }

        // Create new user
        try {
            $user = new User();
            $user->phoneNumber = $request->phoneNumber;
            $user->password = app('hash')->make($request->password);
            $user->invationalCode=$request->invationalCode;

            if ($user->save()) {
                return $this->login($request);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function login(Request $request)
    {
        $phoneNumber = $request->phoneNumber;
        $password = $request->password;


        // Check if field is empty
        if (empty($phoneNumber) or empty($password)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all the fields']);
        }

        // Check if password is less than 6 character
        if (strlen($password) < 6) {
            return response()->json(['status' => 'error', 'message' => 'Password should be min 6 character']);
        }

        //check phoneNumber
        if(!preg_match("/^[0-9]{11}$/", $phoneNumber)) {
            return response()->json(['status' => 'error', 'message' => 'You must provide the correct phoneNumber']);
        }

        $credentials = request(['phoneNumber', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token,$request);
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, Request $request)
    {
        return response()->json([
            'data'=>[
                'phoneNumber'=>$request->phoneNumber,
                'password'=>$request->password,
                'invationalCode'=>$request->invationalCode
            ],
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() *60
        ]);
    }
};
