<?php

namespace OpenApi\Exception;

use OpenApi\BaseResponse;

class NotFoundException extends ApiException
{
    public function __construct($message, $code = null, $prev = null)
    {
        parent::__construct($message, BaseResponse::HTTP_NOT_FOUND, $prev);
    }
}