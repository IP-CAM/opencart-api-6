<?php
require_once("abstract.php");

class ControllerOpenapiHello extends AbstractOpenApi
{

    public function index()
    {
        $this->output(["hello" => "halil"]);
    }

   public function test() {

   }

}
