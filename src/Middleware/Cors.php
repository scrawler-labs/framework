<?php

namespace Scrawler\Middlewares;

use Asm89\Stack\CorsService;
use Scrawler\Interfaces\MiddlewareInterface;
use Scrawler\Scrawler;

class Cors implements MiddlewareInterface
{

    private $cors;

    public function run($request, $next)
    {
        //Middleware logic goes here
        /*
        Example
        $this->cors = new CorsService(array(
        'allowedHeaders'         => array('Origin', 'Content-Type', 'X-Auth-Token', 'Authorization'),
        'allowedMethods'         => array('DELETE', 'GET', 'POST', 'PUT'),
        'allowedOrigins'         => array('localhost/'),
        'allowedOriginsPatterns' => array('/localhost:\d/'),
        'exposedHeaders'         => false,
        'maxAge'                 => false,
        'supportsCredentials'    => false,
        ));
         */
        $config = Scrawler::engine()->config();

        $this->cors = new CorsService(array(
            'allowedHeaders' => $config->get('cors.headers'),
            'allowedMethods' => $config->get('cors.methods'),
            'allowedOrigins' => $config->get('cors.origins'),
            'allowedOriginsPatterns' => $config->get('cors.originpatterns'),
            'exposedHeaders' => false,
            'maxAge' => false,
            'supportsCredentials' => false,
        ));

        // For Preflight, return the Preflight response
        if ($this->cors->isPreflightRequest($request)) {
            $response = $this->cors->handlePreflightRequest($request);

            $this->cors->varyHeader($response, 'Access-Control-Request-Method');

            return $response;
        }

        //Patch response on event in case of any failure
        app()->dispatcher()->subscribeTo('kernel.response', function($event) {
            app()->setResponse($this->addHeaders(app()->request(), app()->response()));
        });

        // Handle the request
        $response = $next($request);

        if ($request->getMethod() === 'OPTIONS') {
            $this->cors->varyHeader($response, 'Access-Control-Request-Method');
        }

        return $this->addHeaders($request, $response);
    }

    protected function addHeaders($request, $response)
    {
        if (!$response->headers->has('Access-Control-Allow-Origin')) {
            // Add the CORS headers to the Response
            $response = $this->cors->addActualRequestHeaders($response, $request);
        }

        return $response;
    }

}
