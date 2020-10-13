<?php
/**
 * Scarawler Filesystem Service
 *
 * @package: Scrawler
 * @author: Pranjal Pandey
 */

namespace Scrawler\Service;

use Scrawler\Scrawler;
use Scrawler\Interfaces\StorageInterface;

class Storage extends \League\Flysystem\Filesystem
{
    /**
     * @var StorageInterface
     */
    protected $adapter;

    /**
     * Constructor.
     *
     * @param StorageInterface $adapter
     * @param \League\Flysystem\Config|array     $config
     */
    public function __construct(StorageInterface $adapter, $config = null)
    {
        parent::__construct($adapter, $config);
    }

    /**
     * Get the Adapter.
     *
     * @return StorageInterface adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }


  
    /**
     * Stores the files in request to  specific path
     *
     * @param string $path
     * @return array path
     */
    public function saveRequest(String $path='')
    {
        $uploaded = [];
        $files= Scrawler::engine()->request()->files->all();
        foreach ($files as $name => $file) {
            if (\is_array($file)) {
                $paths=[];
                foreach ($file as $single) {
                    if($single){
                    $filepath =  $this->writeRequest($single, $path);
                    array_push($paths, $filepath);
                    }
                }
                $uploaded[$name] = $paths;
            } else {
                if($file){
                $uploaded[$name]  = $this->writeRequest($file, $path);
                }
            }
        }
        return $uploaded;
    }

    public function writeRequest($file, $path='',$filename = null)
    {
        $content = file_get_contents($file->getPathname());
        //changes made by pornima on 12/10/2020
        // $filename=md5($file->getClientOriginalName()).uniqid().'.'.$file->getClientOriginalExtension();
        $originalname = explode(".",$file->getClientOriginalName());
        if($filename == null){
            $filename=$originalname[0].'_'.uniqid().'.'.$file->getClientOriginalExtension();
        }else{
            $filename = $filename.'.'.$file->getClientOriginalExtension();
        }
        $this->write($path.$filename, $content);
        return $path.$filename;
    }

    public function getUrl($path)
    {
        return $this->getAdapter()->getUrl($path);
    }
}
