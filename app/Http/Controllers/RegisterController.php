<?php

namespace App\Http\Controllers;



use App\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterController
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    */
    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        //app(UserRequest::class )->scene('B')->validate($request->all());
        //var_dump(session()->getId());
        session()->put('fuck', session()->getId());
    }

    public function check()
    {//var_dump(session()->getId());
        return session()->get('fuck');
    }
}
