<?php

/**
 * Helper function to return instance of scrawler
 * 
 * @return Object \Scrawler\Scrawler
 */
if (! function_exists('s')) {
    function s()
    {
        return Scrawler\Scrawler::engine();
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
        return Scrawler\Scrawler::engine()->template()->render($file, $vars);
    }
}

 /**
  *Generates url with bath
  */
  if (! function_exists('url')) {
      function url($path='')
      {
          return Scrawler\Scrawler::engine()->request()->getSchemeAndHttpHost().Scrawler\Scrawler::engine()->request()->getBasePath().$path;
      }
  }

  /**
   * Returns request object
   */
  if (! function_exists('request')) {
      function request()
      {
          return Scrawler\Scrawler::engine()->request();
      }
  }
