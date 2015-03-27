<?php

namespace OpenApi\Core;

use OpenApi\Exception\ServerException;

class LoaderProxy
{
    private $loader;

    function __construct(\Loader $loader)
    {
        $this->loader = $loader;
    }

    public function controller($route, $args = array())
    {
        return $this->loader->controller($route, $args);
    }

    public function model($model)
    {
        $this->loader->model($model);
    }

    public function view($template, $data = array())
    {
        return $this->loader->view($template, $data);
    }

    public function library($library)
    {
        $this->loader->library($library);
    }

    public function helper($helper)
    {
        $this->loader->helper($helper);
    }

    public function config($config)
    {
        $this->loader->config($config);
    }

    public function language($language)
    {
        $this->loader->language($language);
    }

    public function adminModel($model)
    {
        $file = DIR_APPLICATION . '../admin/model/' . $model . ".php";
        $class = '\\Model' . preg_replace('/[^a-zA-Z0-9]/', '', $model);

        if (file_exists($file)) {
            require_once($file);
            return $class;
        }

        throw new ServerException(sprintf("Requested admin model '%s' not found", $model));
    }
}