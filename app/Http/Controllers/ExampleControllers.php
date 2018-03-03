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
use App\Transformers\UserTransformer;
use App\User;
use Foundation\Http\Request;
use Foundation\Routing\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;


class ExampleControllers extends Controller
{
    /**
     * 容器默认存储的几个实体类 ： log , config , db, events, log, filesystem , validator , translator
     * 工具类 Arr , Str
     */
    public function index()
    {
        return 'hello world';
    }

    public function validator( Request $request)
    {
        //app(UpdateUserRequest::class )->scene('B')->validate($request->all());

//        $rules = [
//            'A' => 'required',
//            'B' => 'boolean',
//            'C' => 'numeric'
//        ];
//
//        $this->validate($request, $rules);
//
//        $validator = $this->getValidationFactory()->make($request->all(),$rules);

        /*$validator = Validator::make($request->all(), [
            'A' => 'required',
            'B' => 'boolean',
            'C' => 'numeric'
        ],[
            'A.required'=>"A是必须的",
            'B.boolean'=>'B是布尔值',
            'C.numeric' => 'C是数值'
        ]);

        // 门面模式

        if ($validator->fails()){
            return new Response([$validator->errors()->first()],400);
        }*/
    }

    public function CookieDemo(Request $request){
        \Illuminate\Support\Facades\Cookie::queue(new Cookie("cookie_test",'test'));

        return (new Response(['cookie'=>$request->cookie()]));//->withCookie(new Cookie('sid', 'sid9999', time()+3600));
    }

    public function SessionDemo(Request $request){

        //$request->session()->put('key', $request->session()->getId());
        //$request->session()->flash('key', 666);
        return $request->session()->get('key');
    }

    public function CacheDemo(Request $request){
         cache()->add("a", "t",10);
          var_dump( cache()->get("a"));
    }

    /**
     * 事件可在 EventServiceProvider 中设置监听事件
     * 将事件类定义在 Events 文件夹中
     */
    public function eventDemo(){
        //app('events')->dispatch("aa");
        event("AAA");
    }



    public function FilesDemo( Request $request ){
        //七牛测试
        /*$disk = Storage::disk('qiniu');
        $a = $disk->putFileAs('', $request->file('photo'), "test_".$request->file('photo')->hashName());
        var_dump($a);*/
    }

    public function encryptDemo()
    {
        //return bcrypt('666');
        //return decrypt(encrypt("666"));
    }

    /**
     * DB 类测试
     *  默认不开启 门面模式 和 Eloquent
     */
    public function dbDemo( Request $request ){
        var_dump(memory_get_usage());
        //db()->table("table");
        //DB::table("cc_apply")->first();
        $user =  User::query()->first();

        //s转换输入，，有待改进
        $a =  transformData( $user, null, $request->input('group', null) );//UserTransformer::class
        var_dump(memory_get_usage());
        return $a;
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

        //$a= dispatch( new SendReminderEmail())->wait(3);
        //var_dump($a);

        $a = dispatchMulti()
            ->addJob(new SendReminderEmail(1))
            ->addJob(new SendReminderEmail(2))
            ->addJob(new SendReminderEmail(3))->execute(); // 栅栏任务， 全部执行结束，一起返回
        var_dump($a);


        return new Response(["message"=>"success"]);

    }


    public function  processDemo()
    {

        var_dump(" ok ok");
    }
}