<?php

class HttpApiException extends Exception
{
    public function __construct($msg, $code, $previous = null) {
        parent::__construct($msg, $code, $previous);
    }
}


abstract class AbstractOpenApi extends Controller
{
    const HTTP_METHOD_POST = "post";
    const HTTP_METHOD_PUT = "put";
    const HTTP_METHOD_GET = "get";
    const HTTP_METHOD_PATCH = "patch";
    const HTTP_METHOD_DELETE = "delete";
    const HTTP_CONTINUE = 100;
    const HTTP_SWITCHING_PROTOCOLS = 101;
    const HTTP_PROCESSING = 102;            // RFC2518
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    const HTTP_NO_CONTENT = 204;
    const HTTP_RESET_CONTENT = 205;
    const HTTP_PARTIAL_CONTENT = 206;
    const HTTP_MULTI_STATUS = 207;          // RFC4918
    const HTTP_ALREADY_REPORTED = 208;      // RFC5842
    const HTTP_IM_USED = 226;               // RFC3229
    const HTTP_MULTIPLE_CHOICES = 300;
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_FOUND = 302;
    const HTTP_SEE_OTHER = 303;
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_USE_PROXY = 305;
    const HTTP_RESERVED = 306;
    const HTTP_TEMPORARY_REDIRECT = 307;
    const HTTP_PERMANENTLY_REDIRECT = 308;  // RFC7238
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_PAYMENT_REQUIRED = 402;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    const HTTP_REQUEST_TIMEOUT = 408;
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    const HTTP_LENGTH_REQUIRED = 411;
    const HTTP_PRECONDITION_FAILED = 412;
    const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    const HTTP_REQUEST_URI_TOO_LONG = 414;
    const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const HTTP_EXPECTATION_FAILED = 417;
    const HTTP_I_AM_A_TEAPOT = 418;                                               // RFC2324
    const HTTP_UNPROCESSABLE_ENTITY = 422;                                        // RFC4918
    const HTTP_LOCKED = 423;                                                      // RFC4918
    const HTTP_FAILED_DEPENDENCY = 424;                                           // RFC4918
    const HTTP_RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425;   // RFC2817
    const HTTP_UPGRADE_REQUIRED = 426;                                            // RFC2817
    const HTTP_PRECONDITION_REQUIRED = 428;                                       // RFC6585
    const HTTP_TOO_MANY_REQUESTS = 429;                                           // RFC6585
    const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;                             // RFC6585
    const HTTP_RETRY_WITH = 449;
    const HTTP_BLOCKED_BY_WINDOWS_PARENTAL_CONTROLS = 450;
    const HTTP_INTERNAL_SERVER_ERROR = 500;
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_BAD_GATEWAY = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    const HTTP_GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;
    const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;                        // RFC2295
    const HTTP_INSUFFICIENT_STORAGE = 507;                                        // RFC4918
    const HTTP_LOOP_DETECTED = 508;                                               // RFC5842
    const HTTP_BANDWIDTH_LIMIT_EXCEEDED = 509;
    const HTTP_NOT_EXTENDED = 510;                                                // RFC2774
    const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;                             // RFC6585

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
        } catch (HttpApiException $e) {
            $this->addStatusHeader($e->getCode(), ["err" => $e->getMessage()]);
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

    /**
     * @return Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    public function notFound($data = [])
    {
        $this->addStatusHeader(self::HTTP_NOT_FOUND, $data);
    }

    public function badRequest($data = [])
    {
        $this->addStatusHeader(self::HTTP_BAD_REQUEST, $data);
    }

    public function serverError($data = [])
    {
        $this->addStatusHeader(self::HTTP_INTERNAL_SERVER_ERROR, $data);
    }

    /**
     * @param array $data
     */
    protected function addStatusHeader($code, $data = [])
    {
        $message = self::getHttpCode($code);
        $this->get("response")->addHeader(sprintf("HTTP/1.1 %s %s", $code, $message));
        $this->jsonResponse(array_merge(["http_error" => $message], (array)$data));
    }

    protected function tryPhpInput()
    {
        $data = trim(file_get_contents("php://input"));
        $decodedData = json_decode($data, true);

        if (!empty($data) && JSON_ERROR_NONE != json_last_error()) {
            $this->badRequest(["error" => "JSON " . json_last_error_msg()]);
            return;
        }

        $this->get("request")->post = array_merge($this->get("request")->post, (array)$decodedData);
    }

    public static function getHttpCode($code)
    {
        $codes = [
            self::HTTP_CONTINUE => "Continue",
            self::HTTP_SWITCHING_PROTOCOLS => "Switching Protocols",
            self::HTTP_PROCESSING => "Processing",
            self::HTTP_OK => "OK",
            self::HTTP_CREATED => "Created",
            self::HTTP_ACCEPTED => "Accepted",
            self::HTTP_NON_AUTHORITATIVE_INFORMATION => "Non-Authoritative Information",
            self::HTTP_NO_CONTENT => "No Content",
            self::HTTP_RESET_CONTENT => "Reset Content",
            self::HTTP_PARTIAL_CONTENT => "Partial Content",
            self::HTTP_MULTI_STATUS => "Multi-Status",
            self::HTTP_ALREADY_REPORTED => "Already Reported",
            self::HTTP_IM_USED => "Im Used",
            self::HTTP_MULTIPLE_CHOICES => "Multiple Choices",
            self::HTTP_MOVED_PERMANENTLY => "Moved Permanently",
            self::HTTP_FOUND => "Found",
            self::HTTP_SEE_OTHER => "See Other",
            self::HTTP_NOT_MODIFIED => "Not Modified",
            self::HTTP_USE_PROXY => "Use Proxy",
            self::HTTP_RESERVED => "Reserved",
            self::HTTP_TEMPORARY_REDIRECT => "Temporary Redirect",
            self::HTTP_BAD_REQUEST => "Bad Request",
            self::HTTP_UNAUTHORIZED => "Unauthorized",
            self::HTTP_PAYMENT_REQUIRED => "Payment Required",
            self::HTTP_FORBIDDEN => "Forbidden",
            self::HTTP_NOT_FOUND => "Not Found",
            self::HTTP_METHOD_NOT_ALLOWED => "Method Not Allowed",
            self::HTTP_NOT_ACCEPTABLE => "Not Acceptable",
            self::HTTP_PROXY_AUTHENTICATION_REQUIRED => "Proxy Authentication Required",
            self::HTTP_REQUEST_TIMEOUT => "Request Timeout",
            self::HTTP_CONFLICT => "Conflict",
            self::HTTP_GONE => "Gone",
            self::HTTP_LENGTH_REQUIRED => "Length Required",
            self::HTTP_PRECONDITION_FAILED => "Precondition Failed",
            self::HTTP_REQUEST_ENTITY_TOO_LARGE => "Request Entity Too Large",
            self::HTTP_REQUEST_URI_TOO_LONG => "Request-URI Too Long",
            self::HTTP_UNSUPPORTED_MEDIA_TYPE => "Unsupported Media Type",
            self::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE => "Requested Range Not Satisfiable",
            self::HTTP_EXPECTATION_FAILED => "Expectation Failed",
            self::HTTP_I_AM_A_TEAPOT => "I'm a teapot",
            self::HTTP_UNPROCESSABLE_ENTITY => "Unprocessable Entity",
            self::HTTP_LOCKED => "Locked",
            self::HTTP_FAILED_DEPENDENCY => "Failed Dependency",
            self::HTTP_RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL => "Unordered Collection",
            self::HTTP_UPGRADE_REQUIRED => "Upgrade Required",
            self::HTTP_PRECONDITION_REQUIRED => "Precondition Required",
            self::HTTP_TOO_MANY_REQUESTS => "Too Many Requests",
            self::HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE => "Request Header Fields Too Large",
            self::HTTP_RETRY_WITH => "Retry With",
            self::HTTP_BLOCKED_BY_WINDOWS_PARENTAL_CONTROLS => "Blocked by Windows Parental Controls",
            self::HTTP_INTERNAL_SERVER_ERROR => "Internal Server Error",
            self::HTTP_NOT_IMPLEMENTED => "Not Implemented",
            self::HTTP_BAD_GATEWAY => "Bad Gateway",
            self::HTTP_SERVICE_UNAVAILABLE => "Service Unavailable",
            self::HTTP_GATEWAY_TIMEOUT => "Gateway Timeout",
            self::HTTP_VERSION_NOT_SUPPORTED => "HTTP Version Not Supported",
            self::HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL => "Variant Also Negotiates",
            self::HTTP_INSUFFICIENT_STORAGE => "Insufficient Storage",
            self::HTTP_LOOP_DETECTED => "Loop Detected",
            self::HTTP_BANDWIDTH_LIMIT_EXCEEDED => "Bandwidth Limit Exceeded",
            self::HTTP_NOT_EXTENDED => "Not Extended",
            self::HTTP_NETWORK_AUTHENTICATION_REQUIRED => "Network Authentication Required",
        ];

        if (isset($codes[$code])) {
            return $codes[$code];
        }
    }
}