<?php
namespace OpenApi\Exception;

class ApiException extends \Exception
{
    public function __construct($message, $code = null, $prev = null)
    {
        parent::__construct($message, $code, $prev);
    }
}