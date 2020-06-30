<?php
namespace Scrawler\Adapters\Storage;

use Scrawler\Scrawler;
use Scrawler\Interfaces\StorageInterface;
use League\Flysystem\Adapter\Local;

class LocalAdapter extends Local implements StorageInterface{

   function __construct(){
       parent::__construct(\Scrawler\Scrawler::engine()->config['general']['storage']);
   }

   function getUrl($path){
      return url('/storage/'.$path);
   }
}