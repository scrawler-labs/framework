<?php
/**
 * Scarawler Api Service
 *
 * @package: Scrawler
 * @author: Pranjal Pandey
 */

namespace Scrawler\Service;

use Scrawler\Scrawler;

class Api
{
      /**
      * Array too store request path info 
      */
      private $path_info;

      /**
      * stores the request method
      */
     private $method;

      /**
      * Stores model
      */
     private $model;

      /**
      * Initialization tasks
      */
     function __construct(){

        $this->path_info = explode('/', Scrawler::engine()->request()->getPathInfo());
        array_shift($this->path_info);
        $this->method =$this->request->getMethod();
        if(isset($this->path_info[1])){
         $this->model = $this->path_info[1];
        }else{
          $this->model = false;
        }
     }

      /**
      * Function detects weather api mode should activate
      * 
      * @return boolean
      */
     public static function isApi(){
        $path_info = explode('/', Scrawler::engine()->request()->getPathInfo());

         if($path_info[1]=='api'){
             return true;
         }
         else{
             return  false;
         }
     }

      /**
      * Function to send back response from Api mode
      * 
      * @return json response json
      */
     public function dispatch(){
         if(!$this->model){
             return json_encode(['error'=>'not found']);
         }
         if($this->method == 'GET'){
             return json_encode($this->get());
         }
         if($this->method == 'POST' && count($this->path_info) == 2){
             return json_encode(['id'=>$this->post()]);
         }
         if($this->method == 'POST' && count($this->path_info) == 3){
             return json_encode([$this->post()]);
         }
         if($this->method == 'DELETE'){
             $this->delete();
             return json_encode(['sucess'=> 'delete query executed' ]);
         }

      }

      /**
      * Function to resolve get query
      */
     private function get(){
         if(count($this->path_info)==3){
             return Scrawler::engine()->db()->get($this->model, $this->path_info[2]);
         }
         if(count($this->path_info)==2){
             return Scrawler::engine()->db()->get($this->model);
         }
         return json_encode(['error'=>'not found']);
     }

      /**
      * Function to resolve post query
      */
     private function post(){
         if(count($this->path_info)==3 && $this->path_info[2] == 'find'){
             return $this->find();
         }

          $model = Scrawler::engine()->db()->create($this->model);
         foreach($_POST as $property => $value){
             $model->$property = $value;
         }
         return Scrawler::engine()->db()->save($model);
     }

      /**
      * Function to resolve delete query
      */
     private function delete(){
         if (count($this->path_info)==3) {
             $model = Scrawler::engine()->db()->get($this->model, $this->path_info[2]);
             Scrawler::engine()->db()->delete($model);
         }
         if (count($this->path_info)==2) {
             return Scrawler::engine()->db()->deleteAll($this->model);
         }
     }

      /**
      * Function to resolve find query
      */
     private function find(){
         foreach($_POST as $var=>$query){
             return Scrawler::engine()->db()->find( $this->model, $query,[ $var] );
         }
     }

}

