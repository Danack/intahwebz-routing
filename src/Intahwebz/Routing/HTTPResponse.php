<?php

namespace Intahwebz\Routing;


class HTTPResponse implements \Intahwebz\Response {

    public $headers = [];

    public $body;

    public $status = 200;

    public $errorText = '';

    private $params;

    private $domain;

    function __construct(\Intahwebz\Domain $domain) {
        $this->domain = $domain;
    }


    function getStatus() {
        return $this->status;
    }
    function getErrorText() {
        return $this->errorText;
    }

    /**
     * @param mixed $codes
     * @return bool
     */
    function isOK($codes = array(200, 201, 204, 206)) {
        if (is_array($codes)) {
            return in_array($this->status, $codes);
        }

        return $this->status === $codes;
    }


    function setResponse($data) {

    }

    function setErrorStatus($params, $errorCode = 501, $errorText = "Blah blah blah") {
        $this->status = $errorCode;
        $this->errorText = $errorText;
        $this->params = $params;
    }

    function setHeader() {

    }

    function send() {
    }

    function	unsetCookie($cookieName) {
        //$domainInfo = DomainManagement::getDomainInfo();
        $domainToSet = $this->domain->getDomainInfo()->rootCanonicalDomain;
        $domainForCookie = '.'.$domainToSet; //leading dot according to http://www.faqs.org/rfcs/rfc2109.html
        setcookie($cookieName, false, time() - (25 * 3600), '/', $domainForCookie);

        if(isset($_COOKIE[$cookieName])){
            unset($_COOKIE[$cookieName]);
        }
    }


    function setCookieVariable($cookieName, $value, $secureOnly = false){
        $timeSeconds = 60 * 60 * 24 * 30; //30 days in the future.

//	if($GLOBALS['outputStarted'] == true){
//		logToFileFatal("Trying to set cookie '$cookieName' but page output has already started. That's bad.");
//		header('X-OUTPUT-STARTED: 12345');
//		return;
//	}

        //TODO - this is bollocks.
        if($secureOnly == true){
            //if(isHTTPSPage() == false){
                throw new \Exception("Setting Cookie [$cookieName] which is meant to be a secure cookie.");
            //}
        }

        $domainToSet = $this->domain->getDomainInfo()->rootCanonicalDomain;
        $domainForCookie = '.'.$domainToSet; //leading dot according to http://www.faqs.org/rfcs/rfc2109.html

        if($value === null){
            throw new \Exception("Trying to set cookie $cookieName to be NULL. Please use unsetCookie to do this, to avoid cookies being accidentally deleted.");
        }

        //TODO - duplicate remove?
        if($secureOnly == true){
            setcookie(
                $cookieName,
                $value,
                time() + $timeSeconds,  //expire
                '/',   //We need to set the root domain explicitly
                $domainForCookie,
                true, //$secure
                true //$httponly
            );
        }
        else{
            setcookie(
                $cookieName,
                $value,
                time() + $timeSeconds,  //expire
                '/',     //We need to set the root domain explicitly
                $domainForCookie,
                false,  //$secure
                true 	//$httponly
            );
        }

        //we also set the $_COOKIE variable so that reading the cookie read the same value on this page as subsequent pages.
        $_COOKIE[$cookieName] = $value;
    }
}


