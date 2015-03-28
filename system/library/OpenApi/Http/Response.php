<?php

namespace OpenApi\Http;

class Response extends BaseResponse
{
    private $data;
    private $headers = array();

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function doResponse()
    {
        return $this->data;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function addHeader($header)
    {
        $this->headers[] = $header;
    }

}