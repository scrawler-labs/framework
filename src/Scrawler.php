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
     * Route Collection Object
     */
    private $routeCollection;

    /**
     * Stores the event dispatcher object
     */
    private $dispatcher;


    /**
     * Stores the module object
     */
    private $module;

    /**
     * Stores the database
     */
    private $db;

    /**
     * Stores cache object
     */
    private $cache;

    /**
     * Stores the configuration form config.ini
     */
    public $config;

    /**
     * Stores the template
     */
    private $template;


    /**
     * Stores the session
     */
    private $session;
    

    /**
     * Stores the mailer
     */
    private $mailer;

    /**
     * Base directory of project
     */
    private $base_dir;

    /**
     * Stores object of pipeline
     */
    private $pipeline;


    /**
     * Initialize all the needed functionalities
     */
    public function __construct()
    {
        $this->base_dir = dirname(\Composer\Factory::getComposerFile());
        
        $this->config = parse_ini_file($this->base_dir."/config/app.ini", true);
        if ($this->config['general']['enviornment'] == "development") {
            $this->registerWhoops();
        }
        $this->init();
        include __DIR__.'/helper.php';
    }

    /**
     * Initialize Scrawler Engine
     */
    private function init()
    {
        self::$scrawler = $this;
        $this->cache = new Cache();

        //Todo add database to travis  test env
        if ($this->config['general']['enviornment'] != "test") {
            $this->db = new Database();
        }
        
        $this->routeCollection = new RouteCollection($this->base_dir.'/app/controllers', 'App\Controllers');
        $this->module = new Module();
        $this->session  = new Session('kfenkfhcnbejd');
        $this->mail = new Mailer(true);
        $this->pipeline =  new Pipeline();
        $this->dispatcher  = new EventDispatcher();
        //templateing engine
        $views = $this->base_dir.'/app/views';
        $cache = $this->base_dir.'/cache/templates';
        $this->template = new Template($views, $cache);
    }

    private function registerWhoops()
    {
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        $whoops->register();
    }

    /**
     * Handle function
     */
    public function handle(\Symfony\Component\HttpFoundation\Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        try {
            $this->request = $request;
       

            $cresponse = $this->pipeline->middleware([
            new \Scrawler\Middleware\Csrf(),
        ])
        ->run($this->request, function ($request) {
            //$response = ;
            $controllerResolver = new ControllerResolver();
            $argumentResolver = new ArgumentResolver();
    
            $engine = new RouterEngine($request, $this->routeCollection);
            $engine->route();
    
    
            if (false === $controller =$controllerResolver->getController($request)) {
                throw new NotFoundHttpException(sprintf('Unable to find the controller for path "%s". The route is wrongly configured.', $request->getPathInfo()));
            }
    
            $arguments = $argumentResolver->getArguments($request, $controller);
            return $controller(...$arguments);
        });

            //print_r( $controller(...$arguments));


            if (!$cresponse instanceof Response) {
                $response = new Response(
                    'Content',
                    Response::HTTP_OK,
                    ['content-type' => 'text/html']
                );
                $response->setContent($cresponse);
            } else {
                $response = $cresponse;
            }

            return $response;
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

        if ($this->config['general']['enviornment']=="development" || $this->config['general']['enviornment']=="test") {
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
     * returns the event dispatcher object
     * @return Object EventDispatcher
     */
    public function &dispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * returns cache object
     * @return Object \Scrawler\Service\Cache
     */
    public function &cache()
    {
        return $this->cache;
    }

    /**
     * Returns route collection object
     * @return Object RouteCollection
     */
    public function &router()
    {
        return $this->routeCollection;
    }

    /**
     * Returns session object
     * @return Object RouteCollection
     */
    public function &session()
    {
        return $this->session;
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
     * Returns module object
     * @return Object \Scrawler\Service\Module
     */
    public function &module()
    {
        return $this->module;
    }

    /**
     * Returns database object
     * @return Object \Scrawler\Service\Database
     */
    public function &db()
    {
        return $this->db;
    }
    
    /**
     * Returns mailer object
     * @return Object \Scrawler\Service\Mailer
     */
    public function &mailer()
    {
        return $this->mailer;
    }

    /**
     * Returns templating engine object
     * @return Object \Scrawler\Service\Template
     */
    public function &template()
    {
        return $this->template;
    }

    /**
     * Returns pipeline object
     * @return Object \Scrawler\Service\Pipeline
     */
    public function &pipeline()
    {
        return $this->pipeline;
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
