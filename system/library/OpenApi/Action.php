<?php

namespace OpenApi;

use OpenApi\Core\Controller;

class Action
{
    private $namespace;
    private $controller;
    private $action;
    private $arguments;

    function __construct($namespace, $controller, $action = Controller::DEFAULT_ACTION, $arguments = array())
    {
        $this->namespace = $namespace;
        $this->controller = $controller;
        $this->action = $action;
        $this->arguments = $arguments;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
    }



}