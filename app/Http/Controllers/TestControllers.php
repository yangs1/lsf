<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-9-18
 * Time: 上午11:41
 */

namespace App\Http\Controllers;


use App\A;
use App\Transformers\UserTransformer;
use Dingo\Api\Http\Response;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use League\Flysystem\Exception;

class TestControllers
{
    use Helpers;
    public function index(Request $request){


      /*  $a = false;
        $request->file('photo')->store("");*/
       /* app("db");
        $a = A::first();*/
        //return $this->response()->item($a, new UserTransformer());
        //throw new Exception("a");
        return ["a"=>2];
    }
}