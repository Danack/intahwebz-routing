<?php


namespace Intahwebz\Routing;


class OverrideRequest extends HTTPRequest{

    
    function __construct(
        $server,// $_SERVER, 
        $get,//$_GET, 
        $post,//$_POST, 
        $files,//$_FILES, 
        $cookie,//$_COOKIE
        array $params 
    ) {
        parent::__construct($server, 
            $get,
            $post, 
            $files, 
            $cookie);

        $allowedParams = array(
            'hostName',
            'scheme',
            'requestParams',
            'path',
            'port',
            'method',
            'clientIP',
            'requestedFormat',
        );

        foreach ($allowedParams as $allowedParam) {
            if (array_key_exists($allowedParam, $params) == true) {
                $this->{$allowedParam} = $params[$allowedParam];
            }
        }
    }
}
