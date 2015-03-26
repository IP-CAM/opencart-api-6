<?php

class Products extends \OpenApi\Core\Controller
{
    public function getIndexAction($id = null)
    {
        $response = array("hello" => "world", "id" => $id);

        return new \OpenApi\JsonResponse($response);
    }

    public function getAttributesAction($id)
    {
        return new \OpenApi\JsonResponse(array("requested_id" => $id));
    }
}