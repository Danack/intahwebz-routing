<?php


namespace Intahwebz\Tests\Routing;

use Intahwebz\Routing\HTTPRequest;


class HTTPRequestTest extends \PHPUnit_Framework_TestCase {

    function testBasic() {

        $request = new HTTPRequest(
            [], //$server,
            [], //$get,
            [], //$post,
            [], //$files,
            [] //$cookie
        );
        
        //TODO - actual assert stuff.
    }
    
}

 