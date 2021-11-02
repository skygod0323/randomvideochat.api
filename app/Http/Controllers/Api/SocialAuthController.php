<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    //
    public function redirect($social, Request $request)
    {
        //return response("<script>console.log('hello'); var success = 'dfsd'; window.parent.success = 'sdf'; console.log(window.parent); console.log(window.parent.success); window.close(); </script>");
        return Socialite::driver($social)->scopes(['email'])->stateless()->redirect();
    }

    public function callback($social)
    {
        //var_dump($social);

        $user = Socialite::driver($social)->user();

        // Auth::login($user, true);
        Session::put('socialUser', array('name' => $user->getName(), 'email' => $user->getEmail()));

        $token = $user->token;

        Session::put('fbToken', $user->token);

        $page = Session::get('page');

        if ($page != null && $page == 'oneminute') {
            return response('<script> window.close(); </script>');
        } else {
            return redirect('/offset');
        }
    }
}
