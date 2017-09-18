<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-9-18
 * Time: 下午12:02
 */

namespace App\Transformers;

use App\A;
use Illuminate\Http\Request;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    public function transform(A $user)
    {
        return [
            'id' => $user->id,
            'name' => $user->book_name,
            'author' => $user->book_author,
            'publishing_house' => $user->book_publishing_house,
        ];
    }
}