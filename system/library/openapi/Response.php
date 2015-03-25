<?php

namespace OpenApi;

class Response extends AbstractResponse
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

}