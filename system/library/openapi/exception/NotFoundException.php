<?php

namespace OpenApi\Exception;

use OpenApi\AbstractResponse;

class NotFoundException extends ApiException
{
    public function __construct($message, $code = null, $prev = null)
    {
        parent::__construct($message, AbstractResponse::HTTP_NOT_FOUND, $prev);
    }
}