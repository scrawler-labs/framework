<?php
/**
 * Scarawler Database Service
 *
 * @package: Scrawler
 * @author: Pranjal Pandey
 */

namespace Scrawler\Service;

use Scrawler\Scrawler;
use RedBeanPHP\Finder;
use RedBeanPHP\OODBBean;
use RedBeanPHP\R;

class Database
{
    private $toolbox;
    private $finder;

    /**
     * Initialize Readbead DB
     */
    public function __construct()
    {
        $config=Scrawler::engine()->config()->all();
        R::setup('mysql:host='.$config['database']['host'].';dbname='.$config['database']['database'], $config['database']['username'], $config['database']['password']);
        $t = R::getToolBox();
        $this->toolbox = $t->getRedBean();
        $this->finder = new Finder($t);
    }

    /**
     * Creates new model to set property anbd save in database
     *
     * @param String $name
     * @return OODBBean
     */
    public function create(String $name)
    {
        return $this->toolbox->dispense($name);
    }
    
    /**
     * Saves the model to the database
     *
     * @param OODBBean $model
     * @return int|string $id of stored record
     */
    public function save(OODBBean $model)
    {
        return $this->toolbox->store($model);
    }


    /**
     * Overriding get method to either get single or all records
     * if get is called call this else call parent override
     * Example use db()->get('users')
     *
     *
     * @return OODBBean|array
     */
    public function __call($name, $arguments)
    {
        if ($name == 'get') {
            if (count($arguments) == 2) {
                return $this->toolbox->load($arguments[0], $arguments[1]);
            }
            if (count($arguments) == 1) {
                return $this->finder->find($arguments[0], null, []);
            }
        }
        return R::__callStatic($name, $arguments);
    }
    
    /**
     * Get record in locked mode
     *
     * @param string $table of model
     * @param int $id of model to retrive
     *
     * @return OODBBean
     */
    public function getForUpdate(String $table, int $id)
    {
        return R::loadForUpdate($table, $id);
    }

    /**
     * Delete a particular record
     * @param OODBBean $model
     * @return void
     */
    public function delete(OODBBean $model)
    {
        $this->toolbox->trash($model);
    }

    /**
     * delete all records from table
     *
     * @param array $models
     * @return void
     */
    public function deleteAll($models)
    {
        foreach ($models as $model) {
            $this->delete($model);
        }
    }

    /**
     * Find and returns array of records
     *
     * @param string $table
     * @param string $query
     * @param array $values
     * @return array
     */
    public function find(String $table, String $query, $values=[])
    {
        return $this->finder->find($table, $query, $values);
    }

    /**
     * Find and return single record
     *
     * @param string $table
     * @param string $query
     * @param array $values
     * @return OODBBean
     */
    public function findOne(string $table, string $query, $values=[])
    {
        return $this->finder->findOne($table, $query, $values);
    }

    /**
     * Find and return single record
     *
     * @param string $table
     * @param string $query
     * @param array $values
     * @return array|array<integer,RedBeanPHP\OODBBean>
     */
    public function findOrCreate($table, $query = null, $values = [])
    {
        $model = $this->finder->findOne($table, $query, $values);
        if (!empty($model)) {
            return $model;
        } else {
            return $this->toolbox->dispense($table);
        }
    }

    /**
     * Find and update a record if id matches else create record
     *
     * @param string $table
     * @param string $query
     * @param array $values
     * @return array|array<integer,RedBeanPHP\OODBBean>
     */
    public function updateOrCreate($table, $id = null)
    {
        $model = $this->toolbox->load($table, $id);
        if (($id != null || $id != 0) && !empty($model)) {
            return $model;
        } else {
            return $this->toolbox->dispense($table);
        }
    }

    /**
     * Bind all value from incoming request to model (aka bean)
     * and saves it.
     *
     * @param OODBBean|String $model
     * @return int|string $id
     */
    public function saveRequest($model)
    {
        if (!($model instanceof OODBBean)) {
            $model = $this->create($model);
        }
       
        foreach (Scrawler::engine()->request()->all() as $key=>$value) {
            if ($key != 'csrf') {
                $model->$key  = $value;
            }
        }
        return $this->save($model);
    }

    /**
     * Bind all value from incoming request to model (aka bean)
     *
     * @param OODBBean|String $model
     * @return OODBBean
     */
    public function bindRequest($model)
    {
        if (!($model instanceof OODBBean)) {
            $model = $this->create($model);
        }

        foreach (Scrawler::engine()->request()->all() as $key=>$value) {
            if ($key != 'csrf') {
                $model->$key  = $value;
            }
        }

        return $model;
    }
}
