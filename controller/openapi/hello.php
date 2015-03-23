<?php

require_once("abstract.php");

class ControllerOpenapiHello extends AbstractOpenApi
{
    public function route()
    {
        return [
           self::HTTP_METHOD_POST => "myTest",
        ];
    }

    public function getName()
    {
        return "hello";
    }


    public function getHelloAction()
    {
        /** @var ModelCatalogCategory $model */
        $model = $this->model("catalog/category");

        /** @var ModelCatalogAttribute $adminModel */
        $adminModel = $this->adminModel("catalog/attribute");

        // $this->notFound(); 404
        // $this->badRequest(); 400
        // $this->serverError(); 500

        $this->jsonResponse([
            "attr_total" => $adminModel->getTotalAttributes(),
            "total_categories" => $model->getTotalCategoriesByCategoryId(),
            "method" => $this->getRequestMethod(),
            "ip" => $this->server("REMOTE_ADDR"),
            "route" => $this->getParam("route"),
            "abc" => $this->getParam("abc", "default_value"),
        ]);

    }

    public function myTestAction()
    {
        $name = $this->getPost("name");

        $this->jsonResponse([
            "hello" => $name,
        ]);
    }

}
