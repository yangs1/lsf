<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 18-3-1
 * Time: 上午10:27
 */

namespace Foundation\Swoole\Process;

use Illuminate\Support\ServiceProvider;

class ProcessServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('process', function ($app) {
            return new ProcessManager( $app );
        });

    }
}