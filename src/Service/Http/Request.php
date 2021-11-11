<?php
/**
 * Scrawler Request Object
 *
 * @package: Scrawler
 * @author: Pranjal Pandey
 */

namespace Scrawler\Service\Http;

use Scrawler\Scrawler;

class Request extends \Symfony\Component\HttpFoundation\Request
{
    /**
     * Magic method $_POST $_GET to get value from key 
     *
     * @param string $key
     * @return string
     */
    public function __get($key)
    {
        $value = $this->request->get($key);
        if ($value == '') {
            $value = $this->query->get($key);
        } 
        if ($value == '' && json_decode($this->getContent())) {
            $value = json_decode($this->getContent())->$key;
        }
        return $value;
    }

    /**
     * Get all property of request
     *
     * @return array
     */
    public function all()
    {
        if ($this->getContent() && json_decode($this->getContent()))
        return array_merge($this->request->all(), $this->query->all(),  json_decode($this->getContent(),true));

        return array_merge($this->request->all(), $this->query->all());

    }

    /**
     * Check id requst has key 
     *
     * @return boolean
     */
    public function has($key)
    {
        if($this->getContent() && json_decode($this->getContent())){
            if(isset(json_decode($this->getContent())->$key))
            return true;
        }
        if($this->request->has($key) || $this->query->has($key)){
            return true;
        }
        return false;
    }
}
