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
                    $filepath =  $this->writeRequest($single, $path);
                    array_push($paths, $filepath);
                }
                $uploaded[$name] = $paths;
            } else {
                $uploaded[$name]  = $this->writeRequest($file, $path);
            }
        }
        return $uploaded;
    }

    private function writeRequest($file, $path='')
    {
        $content = file_get_contents($file->getPathname());
        $filename=md5($file->getClientOriginalName()).uniqid().'.'.$file->getClientOriginalExtension();
        $this->write($path.$filename, $content);
        return $path.$filename;
    }

    public function getUrl($path)
    {
        return $this->getAdapter()->getUrl($path);
    }
}
