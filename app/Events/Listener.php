<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-9-8
 * Time: 上午11:55
 */

namespace App\Events;


class Listener
{
    public function handle($event="", $p=[]){
        var_dump($event);
        var_dump($p);
    }

    public function A()
    {
        echo "A";
    }
}