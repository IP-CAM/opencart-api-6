<?php

abstract class AbstractOpenApi extends Controller
{
    public function __construct($registry) {
        parent::__construct($registry);
    }

    protected function output($data, array $headers = [])
    {
        $this->response->addHeader('Content-Type: application/json');

        foreach ($headers as $header) {
            $this->response->addHeader($header);
        }

        $this->response->setOutput(json_encode($data));
    }

    protected function loadAdminModel($model)
    {

    }
}