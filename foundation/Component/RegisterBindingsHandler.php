<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-11-10
 * Time: 下午3:37
 */

namespace Foundation\Component;

use App\Providers\EventServiceProvider;
use Foundation\Config\ConfigRepository;
use Foundation\Cookie\CookieServiceProvider;
use Foundation\Session\SessionServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Foundation\Log\LogServiceProvider;
use Illuminate\Hashing\HashServiceProvider;

trait RegisterBindingsHandler
{

    protected function registerConfigBindings()
    {
        $this->singleton('config', ConfigRepository::class);
    }

    protected function registerDbBindings()
    {
         $this->loadComponent( ['Illuminate\Database\DatabaseServiceProvider']);
    }


    protected function registerFilesBindings()
    {
        $this->singleton('files', function () {
            return new Filesystem;
        });
    }

    protected function registerFilesystemBindings()
    {
        $this->singleton('filesystem', function (){
            return new FilesystemManager( app() );
        });
    }

    protected function registerEventsBindings()
    {
        $this->loadComponent( ['Illuminate\Events\EventServiceProvider',EventServiceProvider::class]);
    }

    protected function registerLogBindings()
    {
        $this->register(LogServiceProvider::class);
    }

    protected function registerValidatorBindings()
    {//在为注入 db 的时候可能出现异常
        $this->register('Illuminate\Validation\ValidationServiceProvider');
    }

    protected function registerTranslatorBindings()
    {
        $this->singleton('translator', function () {

            $this->instance('path.lang', $this->getLanguagePath());

            $this->register('Illuminate\Translation\TranslationServiceProvider');

            return $this->make('translator');
        });
    }

    protected function registerSessionBindings(){

        $this->loadComponent(SessionServiceProvider::class);
        if ($this->make('config')->get('session.driver') === 'file'){;
            $this->ensureCacheDirectoryExists($this->make('config')->get('session.files'));
        }
    }

    protected function registerCacheBindings()
    {
        $this->loadComponent('Illuminate\Cache\CacheServiceProvider');
    }

    protected function registerRedisBindings()
    {
        return $this->loadComponent(['Illuminate\Redis\RedisServiceProvider'], 'redis');
        // $this->loadComponent('database', ['Illuminate\Redis\RedisServiceProvider'], 'redis');
    }


    protected function registerEncrypterBindings()
    {
        return $this->register('Illuminate\Encryption\EncryptionServiceProvider');
    }

    protected function registerQueueBindings()
    { // 可以直接使用 laravel 的 Illuminate\Queue\QueueServiceProvider ;
        $this->loadComponent('queue', 'Foundation\Queue\QueueServiceProvider');
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerBusBindings()
    {
        $this->singleton('bus', function () {
            $this->register('Illuminate\Bus\BusServiceProvider');

            return $this->make('Illuminate\Contracts\Bus\Dispatcher');
        });

        $this->offsetUnset('Illuminate\Contracts\Bus\Dispatcher');
    }

    public function registerHashBindings()
    {
        $this->register(HashServiceProvider::class );
    }

    public function registerCookieBindings()
    {
        $this->register( CookieServiceProvider::class );
    }
}