<?php
use Scrawler\Scrawler;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * Helper function to return instance of scrawler
 *
 * @return Object \Scrawler\Scrawler
 */
if (! function_exists('app')) {
    function app()
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
    function view($file, $vars=[])
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
          if(Scrawler::engine()->config()->get('general.https')){
            return 'https://'.Scrawler::engine()->request()->getHttpHost().Scrawler::engine()->request()->getBasePath().$path;
          }
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
   * Redirect user with flash data if given
   */
  if (! function_exists('redirect')) {
      function redirect($url,$data=[])
      {
          if(!empty($data)){
            Scrawler::engine()->session()->start();

              foreach($data as $key=>$value){
                Scrawler::engine()->session()->flash($key,$value);
              }
          }   
          return new RedirectResponse($url);
      }
  }

    /**
     * session read and write helper
     */
    if (! function_exists('session')) {
        function session($key,$value=NULL)
        {
            Scrawler::engine()->session()->start();
            if($value == NULL){
                return Scrawler::engine()->session()->$key;
            } else{
                Scrawler::engine()->session()->$key = $value;
            }
        }
    }

    /**
     * session read and write helper
     */
    if (! function_exists('flash')) {
        function flash($key,$messages=NULL)
        {
            Scrawler::engine()->session()->start();
            if($value == NULL){
                return Scrawler::engine()->session()->flash($key);
            } else{
                Scrawler::engine()->session()->flash($key,$messages);
            }
        }
    }



    /**
     *  helper method to check csrf token
     */
    if (! function_exists('csrf_check')) {
        function csrf_check()
        {
            Scrawler::engine()->session()->start();
            if(!Scrawler::engine()->session()->isset('csrf_token')) {
                        return false;
            }

            if (hash_equals(Scrawler::engine()->session()->flash('csrf_token'), Scrawler::engine()->request()->csrf_token)) {
                        return true;
            }

            return false;
        }
    }

     /**
     *  helper method to check csrf token
     */
    if (! function_exists('csrf')) {
        function csrf()
        {
            Scrawler::engine()->session()->start();
            $token = bin2hex(random_bytes(32));
            Scrawler::engine()->session()->flash('csrf_token',$token);

            return $token;
        }
    }

    /**
     * helper function to get file url
     */
    if (! function_exists('storage')) {
        function storage($path)
        {
            return Scrawler::engine()->storage()->getUrl($path);      
        }
    }

    /**
     * helper function to get db model
     */
    if (! function_exists('model')) {
        function model($model)
        {
            return Scrawler::engine()->db()->create($model);      
        }
    }
   
    /**
     * helper function to write log
     */
    if (! function_exists('log')) {
        function log($level,$message,$context=[])
        {
            return Scrawler::engine()->logger()->$level($message,$context);      
        }
    }

    /**
     * helper function to validate request
     */
    if (! function_exists('validate')) {
        function valdate($rules,$messages=[])
        {
            return Scrawler::engine()->validator()->validateRequest($rules,$messages);      
        }
    }

    /**
     * helper function to validate request and write error in flash
     * @return boolean
     */
    if (! function_exists('validateAndFlash')) {
        function valdateAndFlash($rules,$messages=[])
        {
           $validator=Scrawler::engine()->validator()->validateRequest($rules,$messages);  
           if($validator->fails()){
               Scrawler()->session()->flash('errors',$validator->errors()->all());
               return false;
           }else{
               return true;
           }    
        }
    }
   



