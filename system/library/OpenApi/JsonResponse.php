<?php

namespace OpenApi;

class JsonResponse extends BaseResponse
{
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
        // TODO: Implement getHeaders() method.
    }


}