<?php

namespace OpenApi\Core;

use OpenApi\Core;

class Controller extends \Controller
{
    private $core;

    const DEFAULT_ACTION = "indexAction";

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->core = new Core($registry);
    }

    public function index()
    {
        return $this->core->execute();
    }

    /**
     * @return \OpenApi\Http\Request
     */
    public function getRequest()
    {
       return $this->core->getRequest();
    }

    public function get($service)
    {
        return $this->core->get($service);
    }

    public function adminModel($model, $extends = null)
    {
        return $this->core->adminModel($model, $extends);
    }

    public function model($model, $extends = null)
    {
        return $this->core->model($model, $extends);
    }
}