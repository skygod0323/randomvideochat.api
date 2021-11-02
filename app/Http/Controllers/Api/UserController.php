<?php

namespace App\Http\Controllers\Api;

use App\Libs\GoogleAuthenticator;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    //
    public function register(Request $request)
    {
        $input = $request->input();

        if (User::where('email', $input['email'])->count() > 0) {
            return response()->json(['success' => false, 'error' => 'Email exist']);
        }

        $user = new User;

        $user->email = $input['email'];
        $user->password = bcrypt($input['password']);

        $user->save();

        return $this->login($request);
        //return response()->json(['success' => true, 'email' => $user->email]);
    }

    public function registerInfo(Request $request)
    {
        $input = $request->input();

        $user = User::where('email', $input['email'])->first();

        $user->name = $input['name'];
        $user->age = $input['age'];
        $user->gender = $input['gender'];

        $user->save();

        return response()->json([
            'success' => true,
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'age' => $user->age,
            'gender' => $user->gender,
            'left_mins' => $user->left_mins,
            'photo' => $user->photo,
        ]);

    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            // verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Failed', 'success' => false]);
            }
        } catch (JWTException $e) {
            // something went wrong
            return response()->json(['error' => 'token error'], 500);
        }

        $user = Auth::user();

        return response()->json([
            'success' => true,
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'age' => $user->age,
            'gender' => $user->gender,
            'left_mins' => $user->left_mins,
            'photo' => $user->photo,
            'token' => $token
        ]);
    }

    public function getSponser(Request $request)
    {
        $sponser = $request->input('sponser');

        if (isset($sponser) && $sponser != '') {
            $user = User::where('username', $sponser)->first();

            if ($user) {
                return response()->json([
                    'success' => true,
                    'user' => $user,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                ]);
            }
        }
    }

    public function getG2FCode(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response('Not Authorized', 403);
        }

        if (empty($user->g2f_code)) {
            $gAuth = new GoogleAuthenticator();
            $code = $gAuth->generateSecret();

            $user->g2f_code = $code;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'code' => $user->g2f_code
        ]);
    }

    public function setG2F(Request $request)
    {
        $input = $request->input();

        $user = Auth::user();
        $user->allow_g2f = $input['value'];
        if ($user->allow_g2f == 0) {
            $user->g2f_code = null;
        }

        $user->save();

        return response()->json([
            'success' => true,
        ]);
    }

    public function confirmG2FCode(Request $request)
    {
        $input = $request->input();

        if (!isset($input['code'])) {
            return response()->json([
                'success' => false,
            ]);
        }

        $user = Auth::user();

        $code = $input['code'];

        $g2f_code = $user->g2f_code;

        $gAuth = new GoogleAuthenticator();

        if ($gAuth->checkCode($g2f_code, $code)) {
            return response()->json([
                'success' => true,
            ]);
        } else {
            return response()->json([
                'success' => false,
            ]);
        }
    }

    public function confirmG2FCodeWithoutlogin(Request $request)
    {
        $input = $request->input();

        if (!isset($input['code'])) {
            return response()->json([
                'success' => false,
            ]);
        }

        $code = $input['code'];

        $g2f_code = $input['g2f_code'];

        $gAuth = new GoogleAuthenticator();

        if ($gAuth->checkCode($g2f_code, $code)) {
            return response()->json([
                'success' => true,
            ]);
        } else {
            return response()->json([
                'success' => false,
            ]);
        }
    }

    public function changePassword(Request $request)
    {
        $input = $request->input();

        $user = Auth::user();
        if (\Hash::check($input['oldPassword'], $user->password)) {
            $user->password = bcrypt($input['password']);
            $user->save();

            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'error' => 'Current password is wrong.']);
        }
    }

    public function buyMins(Request $request)
    {
        $input = $request->input();

        $user = User::where('email', $input['email'])->first();
        $min = $user->left_mins;
        $min += $input['mins'];
        $user->left_mins = $min;

        $user->save();

        return response()->json([
            'success' => true,
            'left_mins' => $min
        ]);
    }

    public function getLeftMins(Request $request)
    {
        $input = $request->input();

        $user = User::where('email', $input['email'])->first();

        return response()->json([
            'success' => true,
            'left_mins' => $user->left_mins
        ]);
    }

    public function pastOneMin(Request $request)
    {
        $input = $request->input();

        $user = User::where('email', $input['email'])->first();
        $gender = $user->gender;
        $min = $user->left_mins;

        if ($gender == 'male') {
            $min--;
        } else if ($gender == 'female') {
            $min++;
        }


        $user->left_mins = $min;
        $user->save();

        return response()->json([
            'success' => true,
            'left_mins' => $min
        ]);
    }

    public function paidMin(Request $request) {

        $input = $request->input();

        $user = User::where('email', $input['email'])->first();

        $min = $user->left_mins;
        $min = $min - $input['min'];
        $user->left_mins = $min;

        $user->save();

        return response()->json([
            'success' => true,
            'left_mins' => $min
        ]);
    }
}
