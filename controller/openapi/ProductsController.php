<?php

class ProductsController extends \OpenApi\Core\Controller
{
    public function getIndexAction($code)
    {
        /** @var ModelSettingSetting $settingModel */
        $settingModel = $this->adminModel("setting/setting");

        $config = $settingModel->getSetting($code);
        if (empty($config)) {
            throw new \OpenApi\Exception\NotFoundException(sprintf("'%s' adında bir config bulunamadı.", $code));
        }
        return new \OpenApi\Http\JsonResponse($config);
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