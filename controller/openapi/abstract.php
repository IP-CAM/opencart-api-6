<?php

abstract class AbstractOpenApi extends Controller
{

    const HTTP_METHOD_POST = "post";
    const HTTP_METHOD_PUT = "put";
    const HTTP_METHOD_GET = "get";
    const HTTP_METHOD_PATCH = "patch";
    const HTTP_METHOD_DELETE = "delete";

    public function __construct($registry) {
        parent::__construct($registry);
    }

    public abstract function route();

    public abstract function getName();


    public function index()
    {
        $route = $this->route();
        $method = $this->getRequestMethod();

        if (self::HTTP_METHOD_POST == $method) {
            $this->tryPhpInput(); // accepts http raw body
        }

        try {
            if (isset($route[$method])) {
                $actionName = sprintf("%s%s", $route[$method], "Action");
                if (is_callable([$this, $actionName])) {
                    return call_user_func_array([$this, $actionName], []);
                }
            }

            $actionName = sprintf("%s%s%s", $method, ucfirst(strtolower($this->getName())), "Action");
            if (is_callable([$this, $actionName])) {
                return call_user_func_array([$this, $actionName], []);
            }
        } catch (Exception $e) {
            return $this->notFound(array('errorMessage' => $e->getMessage, 'errorCode' => $e->getCode););
        }        
    }

    protected function jsonResponse(array $data = [])
    {
        $this->get("response")->addHeader("Content-Type: application/json");
        $data = json_encode($data);
        $this->response($data);
    }

    public function response($data)
    {
        $this->get("response")->setOutput($data);
    }

    public function model($model)
    {
        $name = sprintf("model_%s", str_replace("/", "_", $model));

        if ($this->getRegistry()->has($name)) {
            return $this->getRegistry()->get($name);
        }

        $this->get("load")->model($model);
        return $this->getRegistry()->get($name);
    }

    public function adminModel($model)
    {
        $name = sprintf("admin_model_%s", str_replace("/", "_", $model));

        if ($this->getRegistry()->has($name)) {
            return $this->getRegistry()->get($name);
        }

        $this->loadAdminModel($model);
        return $this->get($name);
    }

    private function loadAdminModel($model)
    {
        $file = DIR_APPLICATION . '../admin/model/' . $model . ".php";

        $class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $model);

        if (file_exists($file)) {
            include_once($file);

            $this->getRegistry()->set('admin_model_' . str_replace('/', '_', $model), new $class($this->getRegistry()));
            return;
        }

        throw new Exception("Requested admin model '%s' not found", $model);
    }

    protected function get($service)
    {
        if (!$this->getRegistry()->has($service)) {
            throw new RuntimeException(sprintf("Registry doesn't have '%s' service!", $service));
        }
        return $this->$service;
    }

    public function getRequestMethod()
    {
        return strtolower($this->server("REQUEST_METHOD"));
    }

    public function getPost($key, $default = null)
    {
        return (!empty($this->get("request")->post[$key])) ? $this->get("request")->post[$key] : $default;
    }

    public function getParam($key, $default = null)
    {
        return (!empty($this->get("request")->get[$key])) ? $this->get("request")->get[$key] : $default;
    }

    public function getFile($key)
    {
        return (!empty($this->get("request")->file[$key])) ? $this->get("request")->file[$key] : null;
    }

    public function server($key)
    {
        return (!empty($this->get("request")->server[$key])) ? $this->get("request")->server[$key] : null;
    }

    public function getRegistry()
    {
        return $this->registry;
    }

    public function notFound($data = [])
    {
        $this->addStatusHeader(404, $data);
    }

    public function badRequest($data = [])
    {
        $this->addStatusHeader(400, $data);
    }

    public function serverError($data = [])
    {
        $this->addStatusHeader(500, $data);
    }

    /**
     * @param array $data
     */
    protected function addStatusHeader($code, $data = [])
    {
        $message = self::httpCodes($code);
        $this->get("response")->addHeader(sprintf("HTTP/1.1 %s %s", $code, $message));
        $this->jsonResponse(array_merge(["message" => $message], (array)$data));
    }

    protected function tryPhpInput()
    {
        $data = trim(file_get_contents("php://input"));
        $decodedData = json_decode($data, true);

        if (!empty($data) && JSON_ERROR_NONE != json_last_error()) {
            return $this->badRequest(["error" => "JSON " . json_last_error_msg()]);
        }

        $this->get("request")->post = array_merge($this->get("request")->post, (array)$decodedData);
    }

    public static function httpCodes($code = null)
    {
        $codes =  [
            100 => "Continue",
            101 => "Switching Protocols",
            102 => "Processing",
            200 => "OK",
            201 => "Created",
            202 => "Accepted",
            203 => "Non-Authoritative Information",
            204 => "No Content",
            205 => "Reset Content",
            206 => "Partial Content",
            207 => "Multi-Status",
            300 => "Multiple Choices",
            301 => "Moved Permanently",
            302 => "Found",
            303 => "See Other",
            304 => "Not Modified",
            305 => "Use Proxy",
            306 => "Switch Proxy",
            307 => "Temporary Redirect",
            400 => "Bad Request",
            401 => "Unauthorized",
            402 => "Payment Required",
            403 => "Forbidden",
            404 => "Not Found",
            405 => "Method Not Allowed",
            406 => "Not Acceptable",
            407 => "Proxy Authentication Required",
            408 => "Request Timeout",
            409 => "Conflict",
            410 => "Gone",
            411 => "Length Required",
            412 => "Precondition Failed",
            413 => "Request Entity Too Large",
            414 => "Request-URI Too Long",
            415 => "Unsupported Media Type",
            416 => "Requested Range Not Satisfiable",
            417 => "Expectation Failed",
            418 => "I'm a teapot",
            422 => "Unprocessable Entity",
            423 => "Locked",
            424 => "Failed Dependency",
            425 => "Unordered Collection",
            426 => "Upgrade Required",
            449 => "Retry With",
            450 => "Blocked by Windows Parental Controls",
            500 => "Internal Server Error",
            501 => "Not Implemented",
            502 => "Bad Gateway",
            503 => "Service Unavailable",
            504 => "Gateway Timeout",
            505 => "HTTP Version Not Supported",
            506 => "Variant Also Negotiates",
            507 => "Insufficient Storage",
            509 => "Bandwidth Limit Exceeded",
            510 => "Not Extended"
        ];

        if (isset($codes[$code])) {
            return $codes[$code];
        }

        throw new InvalidArgumentException("Requested httpCode is not found");
    }


}