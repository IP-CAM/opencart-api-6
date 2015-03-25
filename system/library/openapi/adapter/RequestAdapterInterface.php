<?php

namespace OpenApi\Adapter;

interface RequestAdapterInterface
{
    public function getPostData();
    public function getGetData();
    public function getServerData();
    public function getFilesData();
    public function getHeaders();
    public function getCookies();
    public function getRequestData();
}