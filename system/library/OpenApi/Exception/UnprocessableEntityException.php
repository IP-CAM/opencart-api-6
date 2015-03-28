<?php

namespace OpenApi\Exception;

use OpenApi\Http\BaseResponse;

class UnprocessableEntityException extends ApiException
{
    public function __construct($message, $code = null, $prev = null)
    {
        parent::__construct($message, BaseResponse::HTTP_UNPROCESSABLE_ENTITY, $prev);
    }
}