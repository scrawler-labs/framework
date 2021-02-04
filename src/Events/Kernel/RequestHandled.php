<?php

namespace Scrawler\Events\Kernel;

use Scrawler\Service\Http\Request;
use Scrawler\Service\Http\Response;

class RequestHandled
{
    public $request;
    public $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;

    }
}
