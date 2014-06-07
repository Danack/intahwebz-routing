<?php


namespace Intahwebz\Routing;


class DefinedRequest extends AbstractRequest implements \Intahwebz\Request {

    function setPath($path) {
        $this->path = $path;
    }
    
    function __construct($params) {

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


    function checkIfModifiedHeader($unixTime) {
        
    }
    
    function getReferrer() {
        return null;
    }

    function getUploadedFile($formFileName) {
        assert(strlen($formFileName) > 0);
        return null;
    }
}
