<?php
use League\Flysystem\Adapter\Local;

return  [
'filesystem' => new Local(__DIR__.'/../storage'),
'cache' => new Scrawler\Adapters\FileCache()
];