<?php
/**
 * Scrawler Core
 * This package is the base container and kernel
 *
 * @package: Scrawler
 * @author: Pranjal Pandey
 */

namespace Scrawler;

use League\Event\EventDispatcher;
use Noodlehaus\Config;
use Scrawler\Events\Kernel;
use Scrawler\Router\ArgumentResolver;
use Scrawler\Router\ControllerResolver;
use Scrawler\Router\RouteCollection;
use Scrawler\Router\RouterEngine;
use Scrawler\Service\Api;
use Scrawler\Service\Cache;
use Scrawler\Service\Database;
use Scrawler\Service\Http\Request;
use Scrawler\Service\Http\Response;
use Scrawler\Service\Http\Session;
use Scrawler\Service\Logger;
use Scrawler\Service\Mailer;
use Scrawler\Service\Module;
use Scrawler\Service\Pipeline;
use Scrawler\Service\Storage;
use Scrawler\Service\Template;
use Scrawler\Service\Validator;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 *  @method mixed pipeline()
 *  @method mixed router()
 *  @method mixed config()
 */
class Scrawler implements HttpKernelInterface
{
    /**
     * Stores class static instance
     */
    public static $scrawler;

    /**
     * Stores the request being processed
     */
    private $request;

    /**
     * Store instance of container
     *
     * @object \DI\Container
     */
    private $container;

    /**
     * Stores the base directory of scrawler project
     */
    private $base_dir;

    /**
     * Check if Scrawler is in API mode
     */
    private $apiMode = false;

    /**
     * Stores the router being used
     */
    private $current_router;

    /**
     * Whoops instnace
     */
    private $whoops;

    /**
     * Scrawler version
     */
    const VERSION = '3.0.0';

    /**
     * Initialize the Scrawler Engine
     *
     * @param String $base_dir
     */
    public function __construct($base_dir)
    {
        self::$scrawler = $this;

        $this->base_dir = $base_dir;
        $this->init();

        include __DIR__ . '/helper.php';
    }

    /**
     * override call function to simulate backward compability
     *
     * @since 2.2.x
     * @return Object
     */
    public function __call($function, $args)
    {
        return $this->container->get($function);
    }

    /**
     * Build container and configuration
     *
     * @return void
     */
    private function init()
    {

        $builder = new \DI\ContainerBuilder();
        $builder->addDefinitions($this->containerConfig());
        $this->container = $builder->build();
        $this->config()->set('general.base_dir', $this->base_dir);
        $this->config()->set('general.storage', $this->base_dir . '/storage');

        if ($this->config()->get('general.env') == "dev" && !$this->apiMode) {
            $this->registerWhoops();
        }
    }

    /**
     * Register Whoops ErrorHandler
     *
     * @return void
     */
    private function registerWhoops()
    {
        $this->whoops = new \Whoops\Run;
        $this->whoops->allowQuit(false);
        $this->whoops->writeToOutput(false);
        $handler = new \Whoops\Handler\PrettyPageHandler;
        $handler->addDataTable('Scrawler', ['version' => self::VERSION]);
        $this->whoops->pushHandler($handler);
    }

    /**
     * Configure DI Container
     *
     * @return array
     */
    private function containerConfig()
    {
        $views = $this->base_dir . '/app/views';
        $cache = $this->base_dir . '/cache/templates';

        $adapter_config = include $this->base_dir . "/config/adapter.php";
        $adapters = [];
        foreach ($adapter_config as $name => $class) {
            $adapters[$name] = \DI\autowire($class);
        }
        $config = [
            'config' => \DI\autowire(Config::class)->constructor($this->base_dir . '/config'),
            'router' => \DI\autowire(RouteCollection::class)
                ->constructor($this->base_dir . '/app/Controllers', 'App\Controllers'),
            'api_router' => \DI\autowire(RouteCollection::class)
                ->constructor($this->base_dir . '/app/Controllers/Api', 'App\Controllers\Api'),
            'db' => \DI\autowire(Database::class),
            'session' => \DI\autowire(Session::class)->constructor(\DI\get('SessionAdapter')),
            'pipeline' => \DI\autowire(Pipeline::class),
            'dispatcher' => \DI\autowire(EventDispatcher::class),
            'cache' => \DI\autowire(Cache::class),
            'mail' => \DI\autowire(Mailer::class),
            'template' => \DI\autowire(Template::class)->constructor($views, $cache),
            'module' => \DI\autowire(Module::class),
            'storage' => \DI\autowire(Storage::class)->constructor(\DI\get('StorageAdapter')),
            'filesystem' => \DI\get('storage'),
            'logger' => \DI\autowire(Logger::class)->constructor(\DI\get('LogAdapter')),
            'validator' => \DI\autowire(Validator::class),

        ];

        return array_merge($adapters, $config);
    }

    /**
     * HttpKernal Handle Implementation
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param [type] $type
     * @param boolean $catch
     * @return void
     */
    public function handle(\Symfony\Component\HttpFoundation\Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {

        //redirect to secure version if https is true
        if (!$request->isSecure() && $this->config()->get('general.https')) {
            return new RedirectResponse('https://' . $this->getBaseUrl() . $this->getPathInfo());
        }

        try {
            $this->request = $request;
            $this->response = null;
            $this->dispatcher()->dispatch(new Kernel('kernel.request'));
            if (Api::isApi()) {
                $this->apiMode = true;
            }

            if ($this->apiMode) {
                $middlewares = $this->config()->get('api_middlewares');
                $this->current_router = $this->api_router();
            } else {
                $middlewares = $this->config()->get('middlewares');
                $this->current_router = $this->router();
            }

            $response = $this->pipeline()->middleware($middlewares)
                ->run($this->request, function ($request) {
                    $engine = new RouterEngine($request, $this->current_router, $this->apiMode);
                    try {
                        $success = $engine->route();
                    } catch (\Scrawler\Router\NotFoundException $e) {
                        if ($this->config()->get('general.autoAPI')) {
                            $success = false;
                        } else {
                            throw $e;
                        }
                    }

                    if (!$success && $this->apiMode) {
                        $api = new Api();
                        return $this->makeResponse($api->dispatch());
                    }

                    $controllerResolver = new ControllerResolver();
                    $argumentResolver = new ArgumentResolver();

                    $controller = $controllerResolver->getController($request);
                    $arguments = $argumentResolver->getArguments($request, $controller);
                    $this->dispatcher()->dispatch(new Kernel('kernel.controller', $controller));

                    return $this->makeResponse($controller(...$arguments));

                });

            return $this->makeResponse($response);
        } catch (\Exception $e) {
            $this->dispatcher()->dispatch(new Kernel('kernel.exception', $e));

            return $this->exceptionHandler($e);
        }
    }

    /**
     * Handel Exception
     *
     * @param Exception $e
     * @return Response
     */
    private function exceptionHandler($e)
    {
        $response = new Response();

        if ($this->apiMode) {
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
            if ($this->config()->get('general.env') != 'prod') {
                $response->setStatusCode(500);
                $response->setContent($this->whoops->handleException($e));
            } else {
                $this->container->get('logger')->error($e->getMessage());
                if ($e instanceof \Scrawler\Router\NotFoundException) {
                    $response->setStatusCode(404);
                    $response->setContent('404 error');
                } else {
                    $response->setStatusCode(500);
                    $response->setContent('Internal error');
                }
            }

        }
        $this->dispatcher()->dispatch(new Kernel('kernel.response'));
        return $response;

    }

    /**
     * Make sure the content is a reponse object
     *
     * @param String|Response $content
     * @return Response
     */
    private function makeResponse($content)
    {
        if (!$content instanceof Response) {

            if (is_array($content)) {
                $content = \json_encode($content);
            }

            if ($this->apiMode) {
                $type = ['content-type' => 'application/json'];
            } else {
                $type = ['content-type' => 'text/html'];
            }

            $response = new Response(
                $content,
                Response::HTTP_OK,
                $type
            );
        } else {
            $response = $content;
        }
        $this->dispatcher()->dispatch(new Kernel('kernel.response'));

        return $response;
    }

    /**
     * Returns request object
     * @return Object Request
     */
    public function &request()
    {
        return $this->request;
    }

    /**
     * Returns scrawler class object
     * @return Object Scrawler\Scrawler
     */
    public static function &engine()
    {
        return self::$scrawler;
    }

    /**
     * Returns scrawler version
     *
     * @return string
     */
    public function getVersion()
    {
        return static::VERSION;
    }
}
