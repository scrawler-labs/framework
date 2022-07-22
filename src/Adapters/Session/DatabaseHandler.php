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

class DatabaseHandler extends AbstractSessionHandler
{
    /**
     * Store Database instance
     *
     * @var \Scrawler\Service\Database
     */
    private $db;

    /**
     * Check if gc is called
     *
     * @var boolean
     */
    private $gcCalled = false;


    public function __construct()
    {
        $this->db = Scrawler::engine()->db();
    }

    protected function doWrite(string $sessionId, string $data):bool
    {
        $maxlifetime = (int) ini_get('session.gc_maxlifetime');
        $session = $this->db->findOne('session', 'sessionid  LIKE ?', [$sessionId]);
        if ($session == null) {
            $session = $this->db->create('session');
        }
        $session->sessionid = $sessionId;
        $session->session_data = $data;
        $session->session_expire = time() + $maxlifetime;
        $this->db->save($session);
        return true;
    }

    protected function doRead(string $sessionId):string
    {
        $session = $this->db->findOne('session', 'sessionid = ? AND session_expire > ?', [$sessionId, time()]);
        if ($session == null) {
            return '';
        }
        return $session->session_data;
    }

    protected function doDestroy(string $sessionId):bool
    {
        $session = $this->db->findOne('session', 'sessionid  LIKE ?', [$sessionId]);
        $this->db->delete($session);
        return true;
    }

    public function updateTimestamp(string $sessionId, string $data):bool
    {
        return true;
    }

    public function gc(int $maxlifetime): int|false
    {
        $this->gcCalled = true;
        return true;
    }

    public function close():bool
    {
        if ($this->gcCalled) {
            $this->gcCalled = false;

            $sessions = $this->db->find('session', 'session_expire < ?', [time()]);
            $this->db->deleteAll($sessions);
        }
        return true;
    }
}
