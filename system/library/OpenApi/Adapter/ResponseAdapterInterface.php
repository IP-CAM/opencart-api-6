<?php

namespace OpenApi\Adapter;

use OpenApi\Http\BaseResponse;

interface ResponseAdapterInterface {
    public function __construct(\Response $response);
    public function adaptee(BaseResponse $apiResponse);
}

