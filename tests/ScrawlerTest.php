<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use Scrawler\Scrawler;
use Scrawler\Service\Module;
use Scrawler\Service\Template;
use Scrawler\Service\Cache;
use Scrawler\Service\Mailer;
use Scrawler\Router\RouteCollection;
use Scrawler\Router\RouterEngine;
use Scrawler\Service\Http\Session;
use Scrawler\Service\Http\Request;
use Scrawler\Service\Pipeline;



class ScrawlerTest extends TestCase
{
  function  testInstanceOf(){
      $this->assertInstanceOf(Cache::class, Scrawler::engine()->cache());
      $this->assertInstanceOf(RouteCollection::class, Scrawler::engine()->router());
      $this->assertInstanceOf(Session::class, Scrawler::engine()->session());
      $this->assertInstanceOf(Template::class, Scrawler::engine()->template());
      $this->assertInstanceOf(Module::class, Scrawler::engine()->module());
      $this->assertInstanceOf(Pipeline::class, Scrawler::engine()->pipeline());
  }

  function testHandle(){
    $request = Request::create(
      '/hello/world/scrawler',
      'GET'
       );

    $response = Scrawler::engine()->handle($request);
    $this->assertInstanceOf(Request::class, Scrawler::engine()->request());
    $this->assertEquals('hello scrawler',$response->getContent());

  }
}