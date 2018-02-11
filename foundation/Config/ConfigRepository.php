<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 18-2-7
 * Time: 下午3:50
 */

namespace Foundation\Config;


use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ConfigRepository extends Repository
{
    /**
     * All of the loaded configuration files.
     *
     * @var array
     */
    protected $loadedConfigurations = [];

    /**
     * Get the specified configuration value.
     * @param string $key
     * @param null $default
     * @return mixed
     * @throws \Exception
     */
    public function get($key, $default = null)
    {
        if ( $file = Str::before($key, '.') ){
            $this->configure( $file );
        }
        return parent::get($key, $default);
    }

    /**
     * Load a configuration file into the application.
     *
     * @param $name
     * @throws \Exception
     */
    public function configure($name)
    {
        if (isset($this->loadedConfigurations[$name])) {
            return;
        }

        $this->loadedConfigurations[$name] = true;

        $path = $this->getConfigurationPath($name);

        if ($path) {
            $this->set($name, require $path);
            return;
        }
        throw new FileException("uncaught config file : {$name}");
    }

    /**
     * Get the path to the given configuration file.
     *
     * If no name is provided, then we'll return the path to the config folder.
     *
     * @param  string|null  $name
     * @return string
     */
    public function getConfigurationPath($name = null)
    {
        $appConfigPath = base_path('config').'/'.$name.'.php';
        if (file_exists($appConfigPath)) {
            return $appConfigPath;
        } elseif (file_exists($path = __DIR__.'/../config/'.$name.'.php')) {
            return $path;
        }
        return null;
    }
}