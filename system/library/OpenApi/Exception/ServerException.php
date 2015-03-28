<?php

namespace OpenApi\Exception;

use OpenApi\Http\BaseResponse;

class ServerException extends ApiException {

    public function __construct($message, $code = null, $prev = null)
    {
        parent::__construct($message, BaseResponse::HTTP_INTERNAL_SERVER_ERROR, $prev);
    }

}