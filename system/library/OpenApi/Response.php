<?php

namespace OpenApi;

class Response extends BaseResponse
{
    private $data;

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
        // TODO: Implement getHeaders() method.
    }


}