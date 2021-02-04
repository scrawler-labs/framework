<?php

namespace Scrawler\Events\Kernel;

use Scrawler\Service\Http\Request;

class RequestController
{
    public $request;
    public $controller;

    public function __construct(Request $request, $controller)
    {
        $this->request = $request;
        $this->controller = $controller;

    }
}
