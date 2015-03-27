<?php
namespace OpenApi\Http;

use OpenApi\Adapter\RequestAdapterInterface;

class Request
{
    const HTTP_METHOD_POST = "POST";
    const HTTP_METHOD_PUT = "PUT";
    const HTTP_METHOD_GET = "GET";
    const HTTP_METHOD_PATCH = "PATCH";
    const HTTP_METHOD_DELETE = "DELETE";
    private $requestAdapter;

    public function __construct(RequestAdapterInterface $requestAdapter)
    {
        $this->requestAdapter = $requestAdapter;
    }

    public function getPost($key, $default = null)
    {
        $posts = $this->requestAdapter->getPost();
        return (isset($posts[$key])) ? $posts[$key] : $default;
    }

    public function getParam($key, $default = null)
    {
        $get = $this->requestAdapter->getParam();
        return (isset($get[$key])) ? $get[$key] : $default;
    }

    public function getFile($key)
    {
        $files = $this->requestAdapter->getFiles();
        return (isset($files[$key])) ? $files[$key] : null;
    }

    public function serverParam($key)
    {
        $key = strtoupper($key);
        $server = $this->requestAdapter->getServerVariables();
        return (isset($server[$key])) ? $server[$key] : null;
    }

    public function header($key)
    {
        $headers = $this->requestAdapter->getHeaders();
        return (isset($headers[$key])) ? $headers[$key] : null;
    }

    public function isPost()
    {
        return self::HTTP_METHOD_POST === $this->requestMethod();
    }

    public function isPut()
    {
        return self::HTTP_METHOD_PUT === $this->requestMethod();
    }

    public function isDelete()
    {
        return self::HTTP_METHOD_DELETE === $this->requestMethod();
    }

    public function isPatch()
    {
        return self::HTTP_METHOD_PATCH === $this->requestMethod();
    }

    public function requestMethod()
    {
        return strtoupper($this->serverParam("request_method"));
    }

    public function contentType()
    {
        $type = $this->serverParam("content_type");
        return (empty($type)) ? $this->getDefaultContentType() : $type;
    }

    public function getDefaultContentType()
    {
        return self::HTTP_CONTENT_TYPE_JSON;
    }
}