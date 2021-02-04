<?php
namespace Scrawler\Events;

class Kernel implements League\Event\HasEventName
{
    /** @var string */
    private $name;
    // Any extra object that needs to be sent with event
    public $object;

    public function __construct(string $name, $object = null)
    {
        $this->name = $name;
        $this->object = $object;
    }

    public function eventName(): string
    {
        return $this->name;
    }
}
