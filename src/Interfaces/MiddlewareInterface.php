<?php

namespace Scrawler\Interfaces;

use \Closure;

interface MiddlewareInterface
{
    public function run($request, Closure $next);
}