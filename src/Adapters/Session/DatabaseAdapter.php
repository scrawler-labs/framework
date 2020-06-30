<?php
/**
 * Adapter for session in database filesystem
 *
 * @package: Scrawler
 * @author: Pranjal Pandey
 */
namespace Scrawler\Adapters\Session;

use Scrawler\Scrawler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

Class DatabaseAdapter extends NativeSessionStorage {

   function __construct(){
       parent::__construct([],new DatabaseHandler);
   }

}