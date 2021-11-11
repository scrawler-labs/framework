<?php

namespace Scrawler\Interfaces;

use \Closure;

interface StorageInterface extends \League\Flysystem\FilesystemAdapter
{


    /**
     * Get file Url
     */
    public function getUrl($path);

    
}
