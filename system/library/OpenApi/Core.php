<?php

namespace OpenApi;

use OpenApi\Adapter\OpenCartRequestAdapter;
use OpenApi\Adapter\OpenCartResponseAdapter;
use OpenApi\Exception\ApiException;
use OpenApi\Exception\NotFoundException;
use OpenApi\Exception\ServerException;

class Core
{
    const PACKAGE = "openapi";

    private $request;
    private $registry;
    private $responseAdapter;

    public function __construct(\Registry $registry)
    {
        $this->registry = $registry;
        $this->request = new Request(new OpenCartRequestAdapter($registry->get("request")));
        $this->responseAdapter = new OpenCartResponseAdapter($registry->get("response"));
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function execute()
    {
        $router = new Router($this->getRequest());
        try {
            $action = $router->route();
            if (!is_callable([$action->getController(), $action->getAction()])) {
                throw new NotFoundException("Page not found");
            }

            $controller = $action->getController();
            $controller = new $controller($this->getRegistry());
            $response = call_user_func_array([$controller, $action->getAction()], $action->getArguments());
            if (!($response instanceof BaseResponse)) {
                throw new ServerException("Unknown response type");
            }
        } catch (ApiException $e) {
            $response = new Response(sprintf("%s occurred with message %s", get_class($e), $e->getMessage()));
            $response->addHeader(sprintf("HTTP/1.1 %s %s", $e->getCode(), BaseResponse::getHttpCodeDescription($e->getCode())));
        }

        $this->responseAdapter->adaptee($response);
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