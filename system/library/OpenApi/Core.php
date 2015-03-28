<?php

namespace OpenApi;

use OpenApi\Adapter\OpenCartRequestAdapter;
use OpenApi\Adapter\OpenCartResponseAdapter;
use OpenApi\Core\LoaderProxy;
use OpenApi\Exception\ApiException;
use OpenApi\Exception\NotFoundException;
use OpenApi\Exception\ServerException;
use OpenApi\Exception\UnprocessableEntityException;
use OpenApi\Http\Request;
use OpenApi\Http\BaseResponse;
use OpenApi\Http\JsonResponse;

class Core
{
    const PACKAGE = "openapi";

    private $request;
    private $registry;
    private $responseAdapter;
    private $loader;

    public function __construct(\Registry $registry)
    {
        $this->registry = $registry;
        $this->request = new Request(new OpenCartRequestAdapter($registry->get("request")));
        $this->responseAdapter = new OpenCartResponseAdapter($registry->get("response"));
        $this->loader = new LoaderProxy($registry->get("load"));
        set_error_handler("\\OpenApi\\Core::errorHandler");
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    public function execute()
    {
        $router = new Router($this->getRequest());
        try {
            $action = $router->route();

            if (!is_callable(array($action->getController(), $action->getAction()))) {
                throw new NotFoundException("Page not found");
            }

            $controller = $action->getController();
            $controller = new $controller($this->getRegistry());

            $reflectionMethod = new \ReflectionMethod($controller, $action->getAction());
            $requiredParams = array();
            if (($numberOfParameters = $reflectionMethod->getNumberOfParameters()) > 0) {
                /** @var \ReflectionParameter $parameter */
                foreach ($reflectionMethod->getParameters() as $parameter) {
                    if ($parameter->isDefaultValueAvailable()) {
                        --$numberOfParameters;
                    } else {
                        $requiredParams[] = $parameter->getName();
                    }
                }
            }

            if (count($action->getArguments()) < $numberOfParameters) {
                throw new UnprocessableEntityException(sprintf("Missing parameter, '%s' required", implode(" or ", $requiredParams)));
            }

            $response = call_user_func_array([$controller, $action->getAction()], $action->getArguments());
            if (!($response instanceof BaseResponse)) {
                throw new ServerException("Unknown response type");
            }
        } catch (ApiException $e) {
            $class = get_class($e);
            $class = substr($class, strrpos($class, "\\") + 1);
            $response = new JsonResponse([
                "message" => $e->getMessage(),
                "code" => $e->getCode(),
                "description" => sprintf("%s occurred with message: %s", $class, $e->getMessage()),
            ]);
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

    /**
     * @return \OpenApi\Core\LoaderProxy
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * @param $service
     * @return null
     * @throws ServerException
     */
    public function get($service)
    {
        if ($this->getRegistry()->has($service)) {
            return $this->getRegistry()->get($service);
        }

        throw new ServerException(sprintf("Unknown service '%s'", $service), BaseResponse::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function adminModel($model)
    {
        $registryName = sprintf("admin_model_%s", str_replace("/", "_", $model));
        try {
            $modelInstance = $this->get($registryName);
        } catch (ServerException $e) {
            $modelInstance = null;
        }

        if (!empty($modelInstance)) {
            return $modelInstance;
        }

        $class = $this->getLoader()->adminModel($model);
        $instance = new $class($this->getRegistry());
        $this->getRegistry()->set($registryName, $instance);

        return $this->adminModel($model);

    }

    public function model($model)
    {
        $registryName = sprintf("model_%s", str_replace("/", "_", $model));
        try {
            $modelInstance = $this->get($registryName);
        } catch (ServerException $e) {
            $modelInstance = null;
        }

        if (!empty($modelInstance)) {
            return $modelInstance;
        }

        $this->getLoader()->model($model);
        return $this->model($model);
    }

    public static function errorHandler($errNo, $errStr, $errFile, $errLine, $context)
    {
        throw new ServerException($errStr);
    }
}