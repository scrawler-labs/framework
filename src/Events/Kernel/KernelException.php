<?php

namespace Scrawler\Events\Kernel;

class KernelException
{
    public $exception;

    public function __construct(\Exception $exception)
    {
        $this->exception = $exception;

    }
}
