<?php

class Controlleropenapiproducts extends \OpenApi\Core\Controller
{
    public function getIndexAction()
    {
        $response = array("hello" => "world");

        return new \OpenApi\JsonResponse($response);
    }
}