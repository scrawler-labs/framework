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

      /**
       * Stores the files in request to  specific path
       *
       * @param string $path
       * @return array
       */
    public function saveRequest(String $path='')
    {
        $uploaded = [];
        $files= Scrawler::engine()->request()->files->all();
        foreach ($files as $name => $file) {
            $content = file_get_contents($file->getPathname());
            $filename=md5($file->getClientOriginalName()).uniqid().'.'.$file->getClientOriginalExtension();
            $this->write($path.$filename, $content);
            $uploaded[$name] =  url('/storage/'.$filename);
        }
        return $uploaded;
    }
}
