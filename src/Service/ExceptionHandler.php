<?php

namespace Scrawler\Service;

use Scrawler\Events\Kernel;
use Scrawler\Scrawler;
use Scrawler\Service\Api;
use Scrawler\Service\Http\Response;

class ExceptionHandler
{
    // Instance of whoops engine to render error
    private $whoops;

    public function __construct()
    {
        $this->whoops = new \Whoops\Run;
        $this->whoops->allowQuit(false);
        $this->whoops->writeToOutput(false);
        $handler = new \Whoops\Handler\PrettyPageHandler;
        $handler->addDataTable('Scrawler', ['version' => Scrawler::VERSION]);
        $this->whoops->pushHandler($handler);
    }

    /**
     * Handl3 Exception
     *
     * @param Exception $e
     * @return Response
     */
    public function handleException($e)
    {
        $response = new Response();
        if (Api::isApi()) {
            $status = 500;
            if ($e instanceof \Scrawler\Router\NotFoundException) {
                $status = 404;
            }
            $response->setStatusCode($status);
            $response->setContent(\json_encode([
                'status' => $status,
                'message' => $e->getMessage(),
            ]));
        } else {
            if (Scrawler::engine()->config()->get('general.env') != 'prod') {
                $response->setStatusCode(500);
                $response->setContent($this->whoops->handleException($e));
            } else {
                Scrawler::engine()->logger()->error($e->getMessage());
                if ($e instanceof \Scrawler\Router\NotFoundException) {
                    $response->setStatusCode(404);
                    $response->setContent('404 error');
                } else {
                    $response->setStatusCode(500);
                    $response->setContent('Internal error');
                }
            }
        }
       
        Scrawler::engine()->setResponse($response);
        Scrawler::engine()->dispatcher()->dispatch(new Kernel('kernel.response'));
        return $response;
    }
    

    public function systemErrorHandler($level, $message, $file, $line)
    {
        $e = $this->whoops->handleError($level, $message, $file, $line);
        if (!is_bool($e)) {
            $response = $this->handleException($e);
            $response->send();
        }
    }

    public function systemExceptionHandler($e)
    {
        $response = $this->handleException($e);
        $response->send();
    }
}
