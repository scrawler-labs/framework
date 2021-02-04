<?php

namespace Scrawler\Events\Kernel;

use Scrawler\Service\Http\Request;

class RequestRecieved
{
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
