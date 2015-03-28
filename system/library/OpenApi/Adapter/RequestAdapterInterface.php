<?php

namespace OpenApi\Adapter;

interface RequestAdapterInterface
{
    public function getPost();
    public function getParam();
    public function getServerVariables();
    public function getFiles();
    public function getHeaders();
    public function getCookies();
    public function getRequest();
}