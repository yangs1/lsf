<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 18-2-24
 * Time: 上午10:41
 */

namespace Foundation\Console;


interface CommonInterface
{
    public function handle();

    public function fail();
}