<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 18-2-24
 * Time: 上午9:58
 */

namespace App\Providers;


use App\Console\Serve;

class ConsoleServiceProvider
{
    /**
     * The console listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
          "serve" => Serve::class,
    ];

    protected $defaultCommands = 'handle';

    public function boot(){

        global $argv;

        $console = isset($argv[1]) ? explode(':', $argv[1] ) : '';

        $class = $this->listen[ strtolower($console[0]) ] ?? null;

        if ( $class && class_exists( $class )){

            $provider = new $class( $argv[0] );

            if ( isset($console[1]) && method_exists( $provider, $console[1])){

                unset( $argv[0], $argv[1] );
                return call_user_func_array([$provider, $console[1]], $argv);

            }
        }
        echo "this console is not find. \n";
        echo "Usage: php startfile.php serve:{start|stop|status|reload}\n";

    }

}