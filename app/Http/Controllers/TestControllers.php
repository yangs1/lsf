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
use Illuminate\Support\Facades\Validator;
use League\Flysystem\Exception;
use Library\Routing\Controller;

class TestControllers extends Controller
{
    use Helpers;
    public function index(Request $request){
    //数据验证
        /*$this->validate($request,
            [
                'a' => 'required',
                'b' => 'required',
            ],
            [
                'a.required'=>":attribute NNNNN"
            ],
            [
                'a'=>"啊"
            ]);*/

      /*  $a = false;  $request->file('photo')->store(""); // 文件存储*/
       /* app("db");
        $a = A::first();
        return $this->response()->item($a, new UserTransformer());*/

        //throw new Exception("a");
        return new Response(['a'=>1]);
    }
}