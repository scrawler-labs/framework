<?php
/**
 * Scrawler Database Handlerfor symfony NativeSessionStorage
 *
 * @package: Scrawler
 * @author: Pranjal Pandey
 */
namespace Scrawler\Adapters\Session;

use Scrawler\Scrawler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\AbstractSessionHandler;

Class DatabaseHandler extends AbstractSessionHandler 
{
    /**
     * Store Database instance
     *
     * @var Scrawler\Service\Database
     */    
    private $db;

    /**
     * Check if gc is called
     *
     * @var boolean
     */
    private $gcCalled = false;


    function __construct(){
        $this->db = Scrawler::engine()->db();
    }

    protected function doWrite(string $sessionId, string $data){
        $maxlifetime = (int) ini_get('session.gc_maxlifetime');
         $session = $this->db->findOne('session', 'sessionid  LIKE ?', [$sessionId]);
        if ($session == null) {
            $session = $this->db->create('session');
        }
        $session->sessionid = $sessionId;
        $session->session_data = $session_data;
        $session->session_expire = time() + $maxlifetime;
        $this->db->save($session);
        return true;
    }

    protected function doRead(string $sessionId){
        $session = $this->db->findOne('session', 'sessionid = ? AND session_expire > ?', [$sessionId, time()]);
        if ($session == null) {
            return '';
        }
        return $session->session_data;
    }

    protected function doDestroy(string $sessionId){
        $session = $this->db->findOne('session', 'sessionid  LIKE ?', [$sessionId]);
        $this->db->delete($session);
        return true;
    }


    public function gc($maxlifetime)
    {
        
        $this->gcCalled = true;
        return true;
    }

    protected function close()
    {
        if ($this->gcCalled) {
            $this->gcCalled = false;

            $sessions = $this->db->find('session', 'session_expire < ?', [time()]);
            $this->db->deleteAll($sessions);
        }
        return true;
    }


}
 


