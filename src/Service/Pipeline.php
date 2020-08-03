<?php
/**
 * pipeline for middleware
 *
 * @package: Scrawler
 * @author: Pranjal Pandey
 */
namespace Scrawler\Service;

use Scrawler\Scrawler;
use InvalidArgumentException;
use Scrawler\Interfaces\MiddlewareInterface;

final class Pipeline{

    private $middlewares;

    public function __construct(array $middlewares = [])
    {
        $this->middlewares = $middlewares;
    }

    /**
     * Add middleware(s) or Pipeline
     * @param  mixed $middlewares
     * @return Pipeline
     */
    public function middleware($middlewares)
    {
        if ($middlewares instanceof Pipeline) {
            $middlewares = $middlewares->toArray();
        }

        if ($middlewares instanceof MiddlewareInterface) {
            $middlewares = [$middlewares];
        }

        if (!is_array($middlewares)) {
            throw new InvalidArgumentException(get_class($middlewares) . " is not a valid middleware.");
        }

        return new static(array_merge($this->middlewares, $middlewares));
    }

    /**
     * Run middleware around core function and pass an
     * object through it
     * @param  mixed  $object
     * @param  \Closure $core
     * @return mixed         
     */
    public function run($object, $core)
    {
        $coreFunction = $this->createCoreFunction($core);

        // Since we will be "currying" the functions starting with the first
        // in the array, the first function will be "closer" to the core.
        // This also means it will be run last. However, if the reverse the
        // order of the array, the first in the list will be the outer layers.
        $middlewares = array_reverse($this->middlewares);

        // We create the onion by starting initially with the core and then
        // gradually wrap it in layers. Each layer will have the next layer "curried"
        // into it and will have the current state (the object) passed to it.
        $completePipeline = array_reduce($middlewares, function($nextMiddleware, $middleware){
            return $this->createMiddleware($nextMiddleware, $middleware);
        }, $coreFunction);

        // We now have the complete onion and can start passing the object
        // down through the layers.
        return $completePipeline($object);
    }

    /**
     * Get the layers of this onion, can be used to merge with another onion
     * @return array
     */
    public function toArray()
    {
        return $this->middlewares;
    }

    /**
     * The inner function of the onion.
     * This function will be wrapped on layers
     * @param  \Closure $core the core function
     * @return \Closure
     */
    private function createCoreFunction(\Closure $core)
    {
        return function($object) use($core) {
            return $core($object);
        };
    }

    /**
     * Get an pipeline middleware function.
     * This function will get the object from a previous layer and pass it inwards
     * @param  MiddlewareInterface|String $nextMiddleware
     * @param  MiddlewareInterface|String $middleware
     * @return \Closure
     */
    private function createMiddleware($nextMiddleware, $middleware)
    {
        return function($object) use($nextMiddleware, $middleware){
            if(is_string($middleware)){
               $middleware = new $middleware;
            }
            return $middleware->run($object, $nextMiddleware);
        };
    }


}