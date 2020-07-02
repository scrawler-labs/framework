<?php
/**
 * Handles modules
 *
 * @package: Scrawler
 * @author: Pranjal Pandey
 */
namespace Scrawler\Service;

use Scrawler\Scrawler;

class Module
{
    /**
     * Array  containing 
     */
    private $modules;

    /**
     * Directory of module curently being registered
     */
    private $dir;

    /**
     * Register a module
     */
    public function register($name,$namespace)
    {
        $this->modules[$name] = $namespace.'\\'.$name;
        $this->dir = Scrawler::engine()->base_dir().'/modules/'.$name;
        $this->registerRoutes($name,$namespace);
    }

    /**
     * Load from registerd modules
     */
    public function load($name)
    {
        if(isset($this->modules[$name])){
            if(is_object($this->modules[$name])){
                return $this->modules[$name];
            }
            $this->modules[$name] = new $this->modules[$name]();
            return $this->modules[$name];
        }
    }

    /**
     * Register routes of module
     */
    private function registerRoutes($name,$namespace)
    {
        Scrawler::engine()->router()->registerDir($name);
        $directory = $this->dir.'/Controllers';
        if ($scan = scandir($directory)) {
            $files = array_slice($scan, 2);
            foreach ($files as $file) {
                if ($file != 'Main.php' && !is_dir($directory.'/'.$file)) {
                    Scrawler::engine()->router()->registerController($name.'/'.\basename($file, '.php'), $namespace.'\\Controllers\\'.\basename($file, '.php'));
                }
            }
        }
    }


    /**
     * Register views of module
     */
    public function registerViews(){
        Scrawler::engine()->template()->addPath($this->dir.'/views');
    }
}
