<?php
/**
 * Scarawler Filesystem Service
 *
 * @package: Scrawler
 * @author: Pranjal Pandey
 */

namespace Scrawler\Service;

use Scrawler\Scrawler;

 Class Filesystem extends \League\Flysystem\Filesystem{
      /**
       * Upload all available file from request
       */
      public function saveRequest($path=''){
          $uploaded = [];
          $files= Scrawler::engine()->request()->files->all();
          foreach($files as $name => $file){
            $content = file_get_contents($file->getPathname());
            $filename=md5($file->getClientOriginalName()).uniqid().'.'.$file->getClientOriginalExtension();
            $this->write($path.$filename, $content);  
            $uploaded[$name] =  url('/storage/'.$filename);     
          }
          return $uploaded;
      }

 }