<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-9-18
 * Time: 上午11:41
 */

namespace App\Http\Controllers;

use App\Task\TestTask;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Library\Routing\Controller;
use Symfony\Component\HttpFoundation\Cookie;

class ExampleControllers extends Controller
{
    /**
     * 容器默认存储的几个实体类 ： log , config , db, events, log, filesystem , validator , translator
     * 工具类 Arr , Str
     */
    public function validateDemo(Request $request){
        $rules = [
            'A' => 'required',
            'B' => 'boolean',
            'C' => 'numeric'
        ];
        $message = [
            'A.required'=>"A是必须的",
            'B.boolean'=>'B是布尔值',
            'C.numeric' => 'C是数值'
        ];
        // Validator::make(); 门面模式
        $validator = $this->getValidationFactory()->make($request->all(),$rules,$message);
        if ($validator->fails()){
            return new Response([$validator->errors()->first()],400);
        }

        $request->file('photo')->store(""); // 文件存储*/
        $request->get('_fd'); // 获取 swoole server 的 fd
        return new Response(["message"=>"success"]);
    }

    /**
     * Cookie 的使用方法
     */
    public function CookieDemo(Request $request){

        return (new Response(['cookie'=>$request->cookie()]));//->withCookie(new Cookie('sid', 'sid9999', time()+3600));
    }

    /**
     * swoole task 调用方法
     */
    public function taskDemo(){

        // 同步任务，可能有返回值
        /*$res = syncTask(function (){
            return  "666";
        });*/

        //task(TestTask::class , ["params"=>"a"]); // 异步

        // 栅栏任务， 全部执行结束，一起返回
        $result = barrier()->task(function (){
            return "o";
        })->task(function (){
            return "k";
        })->execute(0.5);
        return $result;

    }

    /**
     * 事件可在 EventServiceProvider 中设置坚挺事件
     * 将事件类定义在 Events 文件夹中
     */
    public function eventDemo(){
        //app('events')->dispatch("aa");
        dispatch("aa");
    }

    /**
     * DB 类测试
     *  默认不开启 门面模式 和 Eloquent
     */
    public function dbDemo(){
        //db()->table("table");
        DB::table("table")->first();
    }
}