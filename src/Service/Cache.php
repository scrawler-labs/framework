<?php
/**
 * Scarawler ccache Service
 *
 * @package: Scrawler
 * @author: Pranjal Pandey
 */

namespace Scrawler\Service;

use Scrawler\Scrawler;

class Cache
{

    /**
     * store memcache object
     */
    private $memcache;

    /**
     * stores the cache file location
     */
    private $location;

    /**
     *  Stores Config
     */
    private $config;

    /**
     * Constructor overload
     */
    public function __construct()
    {
        $this->config = Scrawler::engine()->config()->all();
        if ($this->config['memcahe']['enabled']) {
            $this->memcache = new \Memcached();
            $this->memcache->addServer($this->config['memcahe']['host'], $this->config['memcahe']['port']);
        }
        $this->location = Scrawler::engine()->config()->get('general.base_dir').'/../../cache/core/';
    }

    /**
     * store data to cache
     *
     * @param string $key the key to store
     * @param string $value the value to  store
     *
     * @return boolean success value
     */
    public function set($key, $value, $type='file')
    {
        if ($type  == 'file') {
            $op = file_put_contents($this->location.$key.'.cache', serialize($value));
            return $op ? true : false;
        }

        if ($type == 'memory' &&  $this->config['memcahe']['enabled']) {
            return $this->memcache->set($key, $value);
        }

        return false;
    }

    /**
     * get data from memcache
     *
     * @param string $key the key  to get data from
     *
     * @return mixed value stored in memcache
     */
    public function get($key, $type = 'file')
    {
        if ($type == 'memory' && $this->config['memcahe']['enabled']) {
            return $this->memcache->get($key);
        }

        if ($type == 'file') {
            return unserialize(file_get_contents($this->location.$key.'.cache'));
        }

        return false;
    }
}
