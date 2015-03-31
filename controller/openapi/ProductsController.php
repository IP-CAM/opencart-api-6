<?php

class ProductsController extends \OpenApi\Core\Controller
{
    public function getIndexAction()
    {
        /** @var ModelCatalogProduct $productModel */
        $productModel = $this->adminModel("catalog/product");
        // @TODO: improve filter criteria
        $filters = array(
            "filter_quantity" => $this->getRequest()->getParam("quantity"),
            "filter_status" => $this->getRequest()->getParam("status"),
            "limit" => $this->get("config")->get("config_limit_admin"),
            "start" => ($this->getRequest()->getParam("page", 1) - 1) * $this->get("config")->get("config_limit_admin")
        );
        $products = $productModel->getProducts($filters);

        return new \OpenApi\Http\JsonResponse($products);
    }

    public function getAttributesAction($id)
    {
        return new \OpenApi\Http\JsonResponse(array("requested_id" => $id));
    }

    public function postIndexAction()
    {

        return new \OpenApi\Http\JsonResponse(array("name" => $this->getRequest()->getPost("name")));
    }
}