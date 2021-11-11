<?php
/**
 * Scrawler Session Service 
 *
 * @package: Scrawler
 * @author: Pranjal Pandey
 */

namespace Scrawler\Service\Http;

use Scrawler\Scrawler;

Class Session extends \Symfony\Component\HttpFoundation\Session\Session{
    /**
     * Magic method to directly set session variable
     *
     * @param string $key
     * @param string $value
     */
    public function __set($key, $value)
    {
        $this->set($key,$value);
    }
    
    /**
     * Magic method to directly get session data
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }
    
   
    /**
     * check if session has key
     * 
     * @param string $key
     * @return bool
     */
    public function has($key){
         if(parent::has($key) || parent::getFlashBag()->has($key)){
            return true;
         }
         return false;
    }
    
    /**
     * legacy isset function
     * 
     * @param string $key
     * @return bool
     */
    public function isset($key){
        $this->has($key);
    }
    

    /**
     * Invalidates the current session.
     *
     * Clears all session attributes and flashes and regenerates the
     * session and deletes the old session from persistence.
     *
     */
    public function stop(){
        $this->invalidate(0);
    }

    /**
     * Flash function
     * 
     * @param string $type
     * @param array $messages
     * @return mixed
     */
    public function flash($type=null, $messages=null)
    {
        if(!is_null($messages)){
            $this->getFlashBag()->set($type,$messages);
        }else{
            if (!is_null($type)) {
                return $this->getFlashBag()->get($type);
            }
            return $this->getFlashBag()->all();
        }


    }

}
