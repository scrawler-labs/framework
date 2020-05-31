<?php

namespace Scrawler\Interfaces;

use \Closure;

interface StorageInterface extends \League\Flysystem\AdapterInterface
{


    /**
     * Get file Url
     */
    public function getUrl($path);

    
}