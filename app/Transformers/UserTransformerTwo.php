<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-9-18
 * Time: 下午12:02
 */

namespace App\Transformers;

use App\User;
use \Foundation\Transformer\AbstractTransformer;

class UserTransformerTwo extends AbstractTransformer
{
    public function transformer($model)
    {
        return [

            'author' =>[
                'a' => 2333,
                'b' =>666,
            ]
        ];
    }


    /* dingo 模式下的 transformer 方法*/
   /* public function transform(User $user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'author' => $user->author,
            'email' => $user->email,
        ];
    }*/
}