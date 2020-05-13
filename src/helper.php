<?php
use Scrawler\Scrawler;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Helper function to return instance of scrawler
 * 
 * @return Object \Scrawler\Scrawler
 */
if (! function_exists('s')) {
    function s()
    {
        return Scrawler::engine();
    }
}

/**
 * Render template  from template engine
 * 
 * @return String rendered body
 */
if (! function_exists('view')) {
    function view($file, $vars)
    {
        return Scrawler::engine()->template()->render($file, $vars);
    }
}

 /**
  *Generates url with bath
  */
  if (! function_exists('url')) {
      function url($path='')
      {
          return Scrawler::engine()->request()->getSchemeAndHttpHost().Scrawler::engine()->request()->getBasePath().$path;
      }
  }

  /**
   * Returns request object
   */
  if (! function_exists('request')) {
      function request()
      {
          return Scrawler::engine()->request();
      }
  }

  /**
   * Redirect user
   */
  if(! function_exists('redirect')){
    function redirect($url){
    return new RedirectResponse($url);
    }
  }
