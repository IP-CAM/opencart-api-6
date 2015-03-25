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
        return $this->core->execute($this);
    }

    /**
     * @return \OpenApi\Request
     */
    public function getRequest()
    {
       return $this->core->getRequest();
    }
}