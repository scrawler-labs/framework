<?php
/**
 * Scarawler Logging Service
 *
 * @package: Scrawler
 * @author: Pranjal Pandey
 */

namespace Scrawler\Service;

use Scrawler\Scrawler;

class Logger extends Monolog\Logger
{
    public function __construct($adapter){
        parent::__construct('Scrawler');
        $this->pushHandler($adapter);
    }

}