<?php
/**
 * Adapter for storing in local filesystem
 *
 * @package: Scrawler
 * @author: Pranjal Pandey
 */
namespace Scrawler\Adapters\Storage;

use Scrawler\Scrawler;
use Scrawler\Interfaces\StorageInterface;
use League\Flysystem\Local\LocalFilesystemAdapter;

class LocalAdapter extends LocalFilesystemAdapter implements StorageInterface{

    public function __construct(){
        parent::__construct(\Scrawler\Scrawler::engine()->config()->get('general.storage'));
    }

    public function getUrl($path){
        return url('/storage/'.$path);
    }
}
