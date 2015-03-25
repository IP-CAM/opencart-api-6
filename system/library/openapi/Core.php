<?php

namespace OpenApi;

use OpenApi\Adapter\OpenCartRequestAdapter;

class Core
{
    const PACKAGE = "openapi";

    private $request;
    private $registry;

    public function __construct(\Registry $registry)
    {
        $this->registry = $registry;
        $this->request = new Request(new OpenCartRequestAdapter($registry->get("request")));
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function execute()
    {
        $router = new Router($this->getRequest());
        $action = $router->route();
        $openCartAction = new \Action(sprintf("%s/%s", $action->getController(), $action->getAction()), $action->getArguments());
        $result = $openCartAction->execute($this->registry);
        if ($result instanceof AbstractResponse) {
            $this->getRegistry()->get("response")->setOutput($result->doResponse());
        }

        return null;
    }

    /**
     * @return \Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }
}