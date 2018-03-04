<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 18-3-4
 * Time: 上午9:13
 */

namespace Foundation\Swoole\Process;


class ClosureProcess extends ProcessInterface
{
    /**
     * @Closure null
     */
    private $closure;

    public function handle()
    {
        ($this->closure)();
    }

    public function setClosure ($closure)
    {
        $this->closure = $closure;
    }
}