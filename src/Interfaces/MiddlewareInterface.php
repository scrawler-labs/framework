<?php

namespace Scrawler\Interfaces;

use \Closure;

interface MiddlewareInterface
{
    public function run($object, Closure $next);
}