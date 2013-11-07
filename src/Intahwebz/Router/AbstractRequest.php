<?php

namespace Intahwebz\Routing;

use Intahwebz\Request;


abstract class AbstractRequest implements Request {

    public $hostName;
    public $scheme;
    public $requestParams = array();
    public $path;
    public $port;
    public $method;
    public $clientIP;
    public $requestedFormat;

    /**
     * This should only be used in exceptional circumstances.
     * @var array
     */
    public $globalVariables = array();

    public $routeParameters = array();

    public $parameters = array(
        'global',	//can set
        'request',	//can override
        'routeParameters', //
        'session', 	//from SESSION //TODO remove - session is storage?
        'cookie',	// from COOKIE //TODO remove - cookies are storage?
    );

    function getHostName(){
        return $this->hostName;
    }

    function setRouteParameters($routeParameters){
        $this->routeParameters = array_merge($this->routeParameters, $routeParameters);
    }

    function getScheme(){
        return $this->scheme;
    }

    function getRequestParams(){
        return $this->requestParams;
    }

    function getPath(){
        return $this->path;
    }

    function getPort(){
        return $this->port;
    }

    function getMethod(){
        return $this->method;
    }

    /**
     * Priority = global request cookie session
     *
     * @param $variableName
     * @param bool $default
     * @param bool $minimum
     * @param bool $maximum
     * @return bool
     */
    function getVariable($variableName, $default = false, $minimum = false, $maximum = false){
        if(array_key_exists($variableName, $this->globalVariables) == true){
            $result = $this->globalVariables[$variableName];
        }
        else if(array_key_exists($variableName, $this->routeParameters) == true){
            $result = $this->routeParameters[$variableName];
        }
        else if(array_key_exists($variableName, $this->requestParams) == true){
            $result = $this->requestParams[$variableName];
        }
        else{
            $result = $default;
        }

        if($minimum !== false){
            if($result < $minimum){
                $result = $minimum;
            }
        }

        if($maximum !== false){
            if($result > $maximum){
                $result = $maximum;
            }
        }

        return $result;
    }
}
