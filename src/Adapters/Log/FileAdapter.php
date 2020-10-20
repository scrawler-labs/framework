<?php
/**
 * Adapter for session in database filesystem
 *
 * @package: Scrawler
 * @author: Pranjal Pandey
 */
namespace Scrawler\Adapters\Log;

use Scrawler\Scrawler;
use Monolog\Handler\RotatingFileHandler;

Class DatabaseAdapter extends RotatingFileHandler {

   public function __construct(){
       parent::__construct('log',10);
   }

}