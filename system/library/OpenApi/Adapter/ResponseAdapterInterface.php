<?php

namespace OpenApi\Adapter;

use OpenApi\BaseResponse;

interface ResponseAdapterInterface {
    public function __construct(\Response $response);
    public function adaptee(BaseResponse $apiResponse);
}

