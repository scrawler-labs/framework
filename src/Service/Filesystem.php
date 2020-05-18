<?php
/**
 * Scarawler Filesystem Service
 *
 * @package: Scrawler
 * @author: Pranjal Pandey
 */

namespace Scrawler\Service;

use Scrawler\Scrawler;

class Filesystem extends \League\Flysystem\Filesystem
{
    public function saveRequest($path='')
    {
        $files= Scrawler::engine()->request()->files->all();
        foreach ($files as $file) {
            $this->write($path, $file);
        }
    }
}
