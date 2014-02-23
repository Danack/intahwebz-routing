<?php


namespace Intahwebz\Routing;


class HTTPRequest extends AbstractRequest {

    private $referedParams = array();

    private $server;
    
    function __construct(
        $server,
        $get,
        $post,
        $files,
        $cookie
    ) {
        $this->determineHostName($server);
        $this->determineScheme($server);
        $this->determineMethod($server);
        $this->determineFormat();
        $this->determineRequestParams($get, $post);
        $this->determineRefererParams($server);
        $this->determinePath($server);
        $this->determinePort($server);
        $this->determineClientIP($server);

        //$this->getNormalizedFILES($files);
        $this->server = $server;
    }

    function determineScheme($server) {
        $this->scheme = 'http';

        if (!empty($server['HTTPS']) && $server['HTTPS'] !== 'off') {
            $this->scheme = 'https';
        }
    }

    function determineRefererParams($server) {
        $this->referedParams = array();

        if(array_key_exists('HTTP_REFERER', $server) == true){
            $queryString = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY);
            $urlInfo = parse_url($queryString, PHP_URL_QUERY);
            parse_str($urlInfo, $this->referedParams);
        }
    }


    function determineMethod($server) {

        $this->method = 'GET';

        if(array_key_exists('REQUEST_METHOD', $server) == true){
            $this->method = mb_strtoupper($server['REQUEST_METHOD']);
        }

//		if ('POST' === $this->method) {
//			'X-HTTP-METHOD-OVERRIDE',
//			'_method',
//			$this->query->get('_method', 'POST'))));
//        }
    }

    function determineFormat() {
        $this->requestedFormat = 'default';
    }

    function determineHostName($server) {

        $this->hostName = 'localhost';

        if(array_key_exists('HTTP_HOST', $server)){
            $this->hostName = $server['HTTP_HOST'];
        }
    }


    function determineRequestParams($get, $post) {
        $this->requestParams = array_merge($get, $post);
    }

    function determinePort($server) {

        $this->port = 80;

        if(array_key_exists('SERVER_PORT', $server)){
            $this->port = intval($server['SERVER_PORT']);
        }

//		if ($this->trustProxy && $this->headers->has('X-Forwarded-Port')) {
//			return $this->headers->get('X-Forwarded-Port');
//		}
    }


    function determinePath($server) {
        $this->path = '/';

        if(array_key_exists('REQUEST_URI', $server)){
            $uriInfo = parse_url($server['REQUEST_URI']);

            $this->path = $uriInfo['path'];
        }
    }

    function determineClientIP($server) {

        $headerArray = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );

        foreach ($headerArray as $key){
            if (array_key_exists($key, $server) === true){
                foreach (explode(',', $server[$key]) as $ip){
                    $ip = trim($ip); // just to be safe

                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){

                        $this->clientIP = $ip;
                        return;
                    }
                }
            }
        }
    }

    function getReferrer() {
        if(array_key_exists("HTTP_REFERER", $this->server)) {
            return $this->server["HTTP_REFERER"];
        }
        return null;
    }

    function getClientIP() {
        return $this->clientIP;
    }

    function checkIfModifiedHeader($unixTime) {
        if (array_key_exists("HTTP_IF_MODIFIED_SINCE", $this->server) == true) {
            if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == @filemtime($finalFilename)) {
                //header("HTTP/1.1 304 Not Modified");
                return "HTTP/1.1 304 Not Modified";
            }
        }
    }
}

