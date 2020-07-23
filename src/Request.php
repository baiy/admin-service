<?php

namespace Baiy\Cadmin;

use Psr\Http\Message\ServerRequestInterface;

class Request
{
    /** @var ServerRequestInterface */
    private $serverRequest;

    public function __construct(ServerRequestInterface $request)
    {
        $this->serverRequest = $request;
    }

    /**
     * @return string
     */
    public function ip(): string
    {
        $server = array_change_key_case($this->serverRequest->getServerParams(), CASE_UPPER);
        if (isset($server['SERVER_NAME'])) {
            return gethostbyname($server['SERVER_NAME']);
        }
        $ip = "";
        if (isset($server)) {
            if (isset($server['SERVER_ADDR'])) {
                $ip = $server['SERVER_ADDR'];
            } elseif (isset($server['LOCAL_ADDR'])) {
                $ip = $server['LOCAL_ADDR'];
            }
        }
        return $ip ?: "";
    }

    /**
     * @return string
     */
    public function method(): string
    {
        return $this->serverRequest->getMethod();
    }

    /**
     * @return string
     */
    public function url(): string
    {
        $server = array_change_key_case($this->serverRequest->getServerParams(), CASE_UPPER);
        $url    = 'http';
        if (isset($server["HTTPS"]) && $server["HTTPS"] == "on") {
            $url .= "s";
        }
        $url .= "://";
        if (isset($server["SERVER_PORT"]) && $server["SERVER_PORT"] != "80") {
            $url .= $server["SERVER_NAME"].":".$server["SERVER_PORT"].$server["REQUEST_URI"];
        } else {
            $url .= $server["SERVER_NAME"].$server["REQUEST_URI"];
        }
        return $url;
    }

    public function input($key = "", $default = null)
    {
        $input = array_merge(
            $this->serverRequest->getQueryParams(),
            $this->serverRequest->getParsedBody()
        );

        if (!$key) {
            return $input;
        }
        return isset($input[$key]) ? $input[$key] : $default;
    }

    public function toArray()
    {
        return [
            'clientIp' => $this->ip(),
            'method'   => $this->method(),
            'url'      => $this->url(),
            'input'    => $this->input(),
        ];
    }
}