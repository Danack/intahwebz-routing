<?php


namespace Intahwebz\Router\Tests;

use Intahwebz\DomainExample;
use Intahwebz\Routing\HTTPResponse;
use Intahwebz\Routing\DefinedRequest;

class ResponseTest extends \PHPUnit_Framework_TestCase {


    function testBasic() {

        $requestDefine = array(
            'hostName' => 'test.local',
            'scheme' => 'http',
            'requestParams' => array(),
            'port' => 80,
            'method' => 'GET'
        );


        $blankRequest = new DefinedRequest($requestDefine);

        $response = new HTTPResponse(new DomainExample($blankRequest, 'basereality.test'));

        $this->assertTrue($response->isOK(), "Default isOK isn't true.");
        $this->assertTrue($response->isOK(200), "Default isOK(200) isn't true.");
        $response->setErrorStatus(['reason' => 'This is a test error']);

        $this->assertSame($response->getStatus(), 501, 'Incorrect default error code');
        $this->assertFalse($response->isOK(), "Error isn't making isOK false.");
    }


}