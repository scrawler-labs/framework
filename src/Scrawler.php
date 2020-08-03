<?php
/**
 * Scarawler core container
 *
 * @package: Scrawler
 * @author: Pranjal Pandey
 */

namespace Scrawler;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernel;
use Scrawler\Router\RouteCollection;
use Scrawler\Router\RouterEngine;
use Scrawler\Router\ArgumentResolver;
use Scrawler\Router\ControllerResolver;
use Scrawler\Service\Database;
use Scrawler\Service\Module;
use Scrawler\Service\Template;
use Scrawler\Service\Cache;
use Scrawler\Service\Mailer;
use Scrawler\Service\Http\Request;
use Scrawler\Service\Http\Session;
use Scrawler\Service\Pipeline;
use Scrawler\Service\Storage;
use Noodlehaus\Config;


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
     * Initialize all the needed functionalities
     */
    public function __construct($base_dir)
    {
        self::$scrawler = $this;

        $this->base_dir = $base_dir;
        $this->init();

        include __DIR__.'/helper.php';
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
     * Initialize Scrawler Engine
     */
    private function init()
    {


        $builder = new \DI\ContainerBuilder();
        $builder->addDefinitions($this->containerConfig());
        $this->container = $builder->build();
        $this->config()->set('general.base_dir',$this->base_dir);
        $this->config()->set('general.storage', $this->base_dir.'/storage');

        if ($this->config()->get('general.env') == "dev") {
            $this->registerWhoops();
        }
    }

    private function registerWhoops()
    {
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        $whoops->register();
    }

    /**
     * Configure DI Container
     *
     * @return array
     */
    private function containerConfig()
    {
        $views = $this->base_dir.'/app/views';
        $cache = $this->base_dir.'/cache/templates';

        $adapter_config = include($this->base_dir."/config/adapter.php");
        $adapters = [];
        foreach ($adapter_config as $name=>$class) {
            $adapters[$name] = \DI\autowire($class);
        }
        $config = [
        'config' => \DI\autowire(Config::class)->constructor($this->base_dir.'/config'),
        'router'=> \DI\autowire(RouteCollection::class)
        ->constructor($this->base_dir.'/app/Controllers', 'App\Controllers'),
        'db' => \DI\autowire(Database::class),
        'session' => \DI\autowire(Session::class)->constructor(\DI\get('sessionAdapter')),
        'pipeline' => \DI\autowire(Pipeline::class),
        'dispatcher' =>  \DI\autowire(EventDispatcher::class),
        'cache' => \DI\autowire(Cache::class),
        'mail' => \DI\autowire(Mailer::class),
        'template' => \DI\autowire(Template::class)->constructor($views, $cache),
        'module' => \DI\autowire(Module::class),
        'storage' => \DI\autowire(Storage::class)->constructor(\DI\get('storageAdapter')),
        'filesystem' => \DI\get('storage'),

        ];

        return array_merge($adapters, $config);
    }


    /**
     * Handle function
     */
    public function handle(\Symfony\Component\HttpFoundation\Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        try {
            $this->request = $request;

            $middlewares = $this->config()->get('middlewares');

            $response = $this->pipeline()->middleware($middlewares)
        ->run($this->request, function ($request) {
            $controllerResolver = new ControllerResolver();
            $argumentResolver = new ArgumentResolver();
    
            $engine = new RouterEngine($request, $this->router());
            $engine->route();
        
            $controller = $controllerResolver->getController($request);

            $arguments = $argumentResolver->getArguments($request, $controller);
            return $this->makeResponse($controller(...$arguments));
        });


            return $this->makeResponse($response);
        } catch (\Exception $e) {
            return $this->exceptionHandler($e);
        }
    }

    /**
     * Handel Exception
     */
    private function exceptionHandler($e)
    {
        $response =  new Response();

        if ($this->config()->get('general.env')!='prod') {
            throw $e;
        } else {
            if ($e instanceof \Scrawler\Router\NotFoundException) {
                $response->setStatusCode(404);
                $response->setContent('404 error');
            } else {
                $response->setStatusCode(500);
                $response->setContent('Internal error');
            }
          
            return  $response;
        }
    }

    /**
     * Make sure the content is a reponse object
     * @return Object Response
     */
    private function makeResponse($content)
    {
        if (!$content instanceof Response) {
            $response = new Response(
                $content,
                Response::HTTP_OK,
                ['content-type' => 'text/html']
            );
        } else {
            $response = $content;
        }
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
}
