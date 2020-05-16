<?php


$app = new Scrawler\Scrawler();
$response = $app->handle(\Scrawler\Service\Http\Request::createFromGlobals());
$response->send();
