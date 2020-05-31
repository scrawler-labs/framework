<?php
namespace Scrawler\Adapters;

use Scrawler\Scrawler;
use Scrawler\Interfaces\StorageInterface;
use League\Flysystem\Adapter\Local;

class StorageAdapter extends Local implements StorageInterface{

   function __construct(){
       parent::__construct(\Scrawler\Scrawler::engine()->config['general']['storage']);
   }

   function getUrl($path){
      return url('/storage/'.$path);
   }
}