<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-9-18
 * Time: 上午11:59
 */

namespace App\Transformers;

use Dingo\Api\Http\Request;
use Dingo\Api\Transformer\Binding;
use Dingo\Api\Contract\Transformer\Adapter;

class TestTransformer implements Adapter
{
    public function transform($response, $transformer, Binding $binding, Request $request)
    {
        // TODO: Implement transform() method.
    }

}