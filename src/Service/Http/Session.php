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
     * To support legacy isset function
     * Caution: for newer version use has() insted
     * 
     * @param string $key
     * @return bool
     */
    public function isset($key){
         if($this->has($key) || $this->getFlashBag()->has($key)){
            return true;
         }
         return false;
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
     * Emulate legacy flash function
     * Since 2.3.0 flash function now returns flash bag too
     * 
     * @param string $type
     * @param string|array $messages
     * @return mixed
     */
    public function flash($type=null, $messages=null)
    {
        if(!is_null($messages)){
            $this->getFlashBag()->set($type,$messages);
        }else{
            if (!is_null($type)) {
                //hacky function to emulate old behavior (would be removed in 2.4.x)
                $messages=$this->getFlashBag()->get($type);
                if (isset($messages[0])) {
                    return $messages[0];
                }
                return '';
            }
            return $this->getFlashBag()->all();
        }


    }

}