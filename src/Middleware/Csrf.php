<?php

namespace Scrawler\Middleware;
use  Scrawler\Interfaces\MiddlewareInterface;

use \Closure;

Class Csrf implements MiddlewareInterface
{
    public function run($request, Closure $next){
        if ($request->getMethod() == 'POST' && $request->request->has('csrf_token')) {
            if (!csrf_check()) {
                throw new \Exception('CSRF token mismatch');
            }
        }   
        return $next($request);
    }
}