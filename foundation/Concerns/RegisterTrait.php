<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-11-10
 * Time: 下午3:37
 */

namespace Foundation\Concerns;

use App\Providers\EventServiceProvider;
use Foundation\Config\Repository;
use Foundation\Cookie\CookieServiceProvider;
use Foundation\Session\SessionServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Foundation\Log\LogServiceProvider;
use Illuminate\Encryption\EncryptionServiceProvider;
use Illuminate\Hashing\HashServiceProvider;

trait RegisterTrait
{

    protected function registerConfigBindings()
    {
        $this->singleton('config', Repository::class);
    }

    protected function registerDatabaseBindings()
    {
         $this->loadComponent('database', ['Illuminate\Database\DatabaseServiceProvider']);
    }


    protected function registerFilesBindings()
    {
        $this->singleton('files', function () {
            return new Filesystem;
        });
    }

    protected function registerFilesSystemBindings()
    {
        $this->configure('filesystems');
        $this->singleton(FilesystemManager::class,function (){
            return new FilesystemManager(app());
        });
    }

    protected function registerEventBindings()
    {
        $this->loadComponent('events',['Illuminate\Events\EventServiceProvider',EventServiceProvider::class]);
    }

    protected function registerLogBindings()
    {
        $this->register(LogServiceProvider::class);
    }

    protected function registerValidatorBindings()
    {//在为注入 db 的时候可能出现异常
        $this->register('Illuminate\Validation\ValidationServiceProvider');
    }

    protected function registerTranslationBindings()
    {
        $this->singleton('translator', function () {

            $this->instance('path.lang', $this->getLanguagePath());

            $this->register('Illuminate\Translation\TranslationServiceProvider');

            return $this->make('translator');
        });
    }

    protected function registerSessionBindings(){

        $this->loadComponent('session',SessionServiceProvider::class);
        if ($this->make('config')->get('session.driver') === 'file'){;
            $this->ensureCacheDirectoryExists($this->make('config')->get('session.files'));
        }
    }

    protected function registerCacheBindings()
    {
        $this->loadComponent('cache', 'Illuminate\Cache\CacheServiceProvider');
    }

    protected function registerRedisBindings()
    {
        $this->singleton('redis', function () {
            return $this->loadComponent(
                'database',
                ['Illuminate\Redis\RedisServiceProvider'],
                'redis'
            );
        });
        // $this->loadComponent('database', ['Illuminate\Redis\RedisServiceProvider'], 'redis');
    }


    protected function registerEncrypterBindings()
    {
        return $this->register('Illuminate\Encryption\EncryptionServiceProvider');
    }

    protected function registerQueueBindings()
    {
        $this->loadComponent('queue', 'Foundation\Queue\QueueServiceProvider');
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerBusBindings()
    {
        $this->singleton('Illuminate\Contracts\Bus\Dispatcher', function () {
            $this->register('Illuminate\Bus\BusServiceProvider');

            return $this->make('Illuminate\Contracts\Bus\Dispatcher');
        });
    }

    public function registerHashingBindings()
    {
        $this->register(HashServiceProvider::class );
    }

    public function registerCookieBindings()
    {
        $this->register( CookieServiceProvider::class );
    }
}