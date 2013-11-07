<?php


namespace Intahwebz\Routing;


class MockHTTPRequest extends AbstractRequest {

    function __construct() {
        $this->method = 'GET';
    }

    function setRequestParams($requestParams) {
        $this->requestParams = array_merge($this->requestParams, $requestParams);
    }

    /**
     * @param $formFileName
     * @return \Intahwebz\UploadedFile
     * @throws \Intahwebz\FileUploadException
     * @throws \Exception
     */
    function getUploadedFile($formFileName) {
        // TODO: Implement getUploadedFile() method.
    }

    function getReferrer() {
        // TODO: Implement getReferrer() method.
    }
}