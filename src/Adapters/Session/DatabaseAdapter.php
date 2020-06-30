<?php
/**
 * Adapter for session in database filesystem
 *
 * @package: Scrawler
 * @author: Pranjal Pandey
 */
namespace Scrawler\Adapters\Session;

use Scrawler\Scrawler;

Class DatabaseAdapter extends NativeSessionStorage {

   function __construct(){
       parent::__construct([],new DatabaseHandler);
   }

}