<?php

namespace OpenApi;

class JsonResponse extends BaseResponse
{

    private $headers = array();
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function doResponse()
    {
        return json_encode($this->data);
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