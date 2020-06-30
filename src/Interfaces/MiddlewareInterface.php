<?php

namespace Scrawler\Interfaces;

use \Closure;

interface MiddlewareInterface
{   
    /** 
    * @param  \Scrawler\Service\Http\Request  $request
    * @param  mixed $next
    * @return mixed         
    */
    public function run($request, $next);
}