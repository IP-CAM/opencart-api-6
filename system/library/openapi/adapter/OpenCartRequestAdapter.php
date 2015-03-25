<?php

namespace OpenApi\Adapter;

class OpenCartRequestAdapter implements RequestAdapterInterface
{
    private $post;
    private $get;
    private $server;
    private $files;
    private $cookies;
    private $request;
    private $headers;

    public function __construct(\Request $request)
    {
        $this->files = $request->files;
        $this->server = $request->server;
        $this->get = $request->get;
        $this->post = $request->post;
        $this->cookies = $request->cookie;
        $this->request = $request->request;
        $this->headers = null;
    }

    /**
     * @return array|null
     */
    public function getRequestData()
    {
        return !empty($this->request) ? $this->request : null;
    }

    /**
     * @return array|null
     */
    public function getCookies()
    {
        return !empty($this->cookies) ? $this->cookies : null;
    }

    /**
     * @return array|null
     */
    public function getPostData()
    {
        return !empty($this->post) ? $this->post : null;
    }

    /**
     * @return array|null
     */
    public function getGetData()
    {
        return !empty($this->get) ? $this->get : null;
    }

    /**
     * @return array|null
     */
    public function getServerData()
    {
        return !empty($this->server) ? $this->server : null;
    }

    /**
     * @return array|null
     */
    public function getFilesData()
    {
        return !empty($this->files) ? $this->files : null;
    }

    /**
     * @return array|null
     */
    public function getHeaders()
    {
        return !empty($this->headers) ? $this->headers : null;;
    }

}