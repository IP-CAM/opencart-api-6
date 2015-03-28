<?php

namespace OpenApi\Adapter;


use OpenApi\Http\BaseResponse;

class OpenCartResponseAdapter implements ResponseAdapterInterface
{

    private $ocResponse;

    public function __construct(\Response $response)
    {
        $this->ocResponse = $response;
    }

    public function adaptee(BaseResponse $apiResponse)
    {
        $headers = $apiResponse->getHeaders();
        if (!empty($headers)) {
            foreach ($headers as $header) {
                $this->ocResponse->addHeader($header);
            }
        }

        $this->ocResponse->setOutput($apiResponse->doResponse());
    }


}