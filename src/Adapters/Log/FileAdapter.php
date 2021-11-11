<?php
/**
 * Adapter for log in file
 *
 * @package: Scrawler
 * @author: Pranjal Pandey
 */
namespace Scrawler\Adapters\Log;

use Scrawler\Scrawler;
use Monolog\Handler\RotatingFileHandler;

Class FileAdapter extends RotatingFileHandler {

    public function __construct(){
        parent::__construct('logs/log',10);
    }

}