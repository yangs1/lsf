<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-9-18
 * Time: 上午11:41
 */

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Jobs\SendReminderEmail;
use App\Task\TestTask;
use App\Transformers\UserTransformer;
use Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Foundation\Routing\Controller;
use Swoole\Mysql\Exception;
use Symfony\Component\HttpFoundation\Cookie;

class ExampleControllers extends Controller
{
    /**
     * 容器默认存储的几个实体类 ： log , config , db, events, log, filesystem , validator , translator
     * 工具类 Arr , Str
     */
    public function validateDemo(Request $request){

        app(UpdateUserRequest::class )->scene('B')->validate($request->all());

        $this->validate($request,[
            'A' => 'required',
            'B' => 'boolean',
            'C' => 'numeric'
        ]);

       /* $message = [
            'A.required'=>"A是必须的",
            'B.boolean'=>'B是布尔值',
            'C.numeric' => 'C是数值'
        ];*/
        // Validator::make(); 门面模式
        $validator = $this->getValidationFactory()->make($request->all(),$rules);
        if ($validator->fails()){
            return new Response([$validator->errors()->first()],400);
        }

      //七牛测试
      /*  $disk = Storage::disk('qiniu');
        $a = $disk->putFileAs('', $request->file('photo'), "test_".$request->file('photo')->hashName());*/


        //$a= $request->file('photo')->store("test_", ['disk'=>'qiniu']); // 文件存储*/
       // $request->get('_fd'); // 获取 swoole server 的 fd
        return new Response(["message"=>"success"]);
    }

    /**
     * Cookie 的使用方法
     */
    public function CookieDemo(Request $request){
        //Cookie::queue(new \Symfony\Component\HttpFoundation\Cookie("cookie_test",'test'));

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


        //var_dump(memory_get_usage());

        var_dump(microtime(true));
        dispatch( new SendReminderEmail())->delay(2);
        /*$a = dispatchMulti([new SendReminderEmail(), new SendReminderEmail()]);
        var_dump($a);*/
        //var_dump(memory_get_usage());
        return new Response(["message"=>"success"]);
        // 栅栏任务， 全部执行结束，一起返回


    }

    /**
     * 事件可在 EventServiceProvider 中设置监听事件
     * 将事件类定义在 Events 文件夹中
     */
    public function eventDemo(){
        //app('events')->dispatch("aa");
        event("aa");
    }

    /**
     * DB 类测试
     *  默认不开启 门面模式 和 Eloquent
     */
    public function dbDemo(){
        //db()->table("table");
        DB::table("table")->first();
    }

    public function index(Request $request)
    {
        //UpdateUserRequest::validate($request->all());

       // var_dump(get_class($request->session()));
      //  $request->session()->put('key', $request->session()->getId());
      /*  $a= db()->table("cc_apply")->take(5)->runSelect();
        return new Response(["message"=>  gettype($a)]);*/
return ["message"=>"success1"];
        //return new Response(["message"=>"success1"]);
       // cache()->add("a", "t",10);
      //  var_dump( cache()->get("a"));
   //  throw new Exception('s');
        //$request->session()->put('key', $request->session()->getId());
        //var_dump(get_class(\db()->table("cc_banner")->get()));

        // return new Response(["message"=>transformData(DB::table("cc_banner")->get(), new UserTransformer())]);
    }
}