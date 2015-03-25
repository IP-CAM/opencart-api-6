<?php

namespace OpenApi;

class JsonResponse extends AbstractResponse
{
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function doResponse()
    {
        return json_encode($this->data);
    }


}