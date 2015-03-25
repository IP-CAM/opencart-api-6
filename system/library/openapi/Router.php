<?php

namespace OpenApi;

use OpenApi\Core\Controller;
use OpenApi\Exception\NotFoundException;

class Router
{
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return Action
     * @throws NotFoundException
     */
    public function route()
    {
        $requestedAction = $this->request->getParam(Core::PACKAGE);
        return $this->parse($requestedAction);
    }

    private function parse($action)
    {
        if (empty($action)) {
            throw new NotFoundException("Request endpoint cannot be empty");
        }

        $parts = explode("/", $action);
        $controller = array_shift($parts);
        $method = array_shift($parts);
        $arguments = $parts;


        $action = new Action(sprintf("%s/%s", Core::PACKAGE, $controller));
        $action->setArguments($arguments);

        $requestMethod = strtolower($this->request->requestMethod());
        if (!empty($method) && "index" != $method) {
            $action->setAction(sprintf("%s%sAction", strtolower($requestMethod), ucfirst($method)));
        } else {
            $action->setAction(strtolower($requestMethod) . ucfirst(Controller::DEFAULT_ACTION));
        }

        return $action;
    }
}
