<?php

namespace OpenApi;

use OpenApi\Core\Controller;
use OpenApi\Exception\NotFoundException;
use OpenApi\Util\TextUtil;
use OpenApi\Http\Request;

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

        $httpRequestMethod = strtolower($this->request->requestMethod());

        $parts = explode("/", $action);
        $controller = sprintf("%s %s", array_shift($parts), "Controller");
        $method = array_shift($parts);
        $arguments = $parts;

        $action = new Action(Core::PACKAGE, TextUtil::makeCamelCase($controller));
        $defaultAction = strtolower($httpRequestMethod) . ucfirst(Controller::DEFAULT_ACTION);
        $requestedMethod = sprintf("%s%sAction", strtolower($httpRequestMethod), TextUtil::makeCamelCase($method));

        $classFile = sprintf("%scontroller/%s/%s.php", DIR_APPLICATION, $action->getNamespace(), $action->getController());
        if (is_file($classFile)) {
            require_once($classFile);
        } else {
            throw new NotFoundException("Requested endpoint not found");
        }

        if (!is_callable([$action->getController(), $requestedMethod])) {
            if (!empty($method)) {
                $arguments[] = $method;
            }
            $method = null;
        }


        $action->setArguments($arguments);

        if (!empty($method) && "index" != $method && preg_match("/^[a-z]/", $method)) {
            $action->setAction($requestedMethod);
        } else {
            $action->setAction($defaultAction);
        }

        return $action;
    }
}
