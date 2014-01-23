<?php


namespace Intahwebz\Tests\Routing;

use Intahwebz\Cache\NullObjectCache;

use Intahwebz\Request;
use Intahwebz\Routing\Router;
use Intahwebz\Routing\DefinedRequest;

class RouterTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Router
     */
    protected $router;

    private $standardRequestDefine = array(
        'hostName' => 'test.local',
        'scheme' => 'http',
        'requestParams' => array(),
        'port' => 80,
        'method' => 'GET',
        'path' => '/',
    );


    protected function setUp(){
        $objectCache = new NullObjectCache();

        $requestDefine = array(
            'hostName' => 'test.local',
            'scheme' => 'http',
            'requestParams' => array(),
            'port' => 80,
            'method' => 'GET'
        );

        $blankRequest = new DefinedRequest($requestDefine);

        $domain = new \Intahwebz\DomainExample($blankRequest, 'basereality.test');

        $this->router = new Router($domain,
            $objectCache, 
            "RouteUnitTests", 
            dirname(__DIR__)."/Tests/RouteTestData.php"
        );
    }

    protected function tearDown(){
    }


    public function checkRouteValid($expection, Request $request)
    {
        $matchedRoute = $this->router->matchRouteForRequest($request);

        $route = $matchedRoute->getRoute();

        if (array_key_exists('routeName', $expection) == true) {
            $this->assertEquals($expection['routeName'], $route->getName());
        }

        if (array_key_exists('classMethod', $expection) == true) {
           $this->assertTrue(false, "This is never meant to be used anymore.");
        }

        if (array_key_exists('callable', $expection) == true) {
            $this->assertEquals($expection['callable'], $route->getCallable());
        }

        if (array_key_exists('routeParams', $expection) == true) {
            foreach ($expection['routeParams'] as $routeParam) {
                $routeParamName = $routeParam[0];
                $routeParamValue = $routeParam[1];
                
                $mergedParams = $matchedRoute->getMergedParameters($request, []);

                $matchedRoute->getParams();
                
                $this->assertArrayHasKey($routeParamName, $mergedParams);

                $this->assertEquals(
                    $routeParamValue, 
                    $mergedParams[$routeParamName], 
                    "Route param doesn't match expected value:"
                );
            }
        }
    }

    public function testRoutes()
    {
        $requestDefine = array(
            'hostName' => 'test.local',
            'scheme' => 'http',
            'requestParams' => array(),
            'port' => 80,
            'method' => 'GET'
        );

        $blankRequest = new DefinedRequest($requestDefine);

        $testDataArray = array(
            [	//simplest test
                'routeName' => 'blogIndex',
                'callable' => ['BaseReality\\Controller\\Blog', 'displayIndex'],
                'path' => '/',
            ],

            [	//Extract variable.
                'path' => '/blog/5/BlogTitle',
                'routeName' => 'blogPost',
                'callable' => ['BaseReality\\Controller\\Blog', 'display'],
                'routeParams' => [
                    ['blogPostID', 5,],
                    ['title', 'BlogTitle']
                ]
            ],

            [	//extract two variables.
                'path' => '/blog/5/BlogTitle.text',
                'routeName' => 'blogPost',
                'callable' => ['BaseReality\\Controller\\Blog', 'display'],
                'routeParams' => [
                     ['blogPostID', 5,],
                     ['title', 'BlogTitle'],
                     ['format', 'text']
                 ]
            ],

            [	//Test last slash not present
                'path' => '/blog/upload',
                'routeName' => 'blogUpload',
                'callable' => ['BaseReality\\Controller\\Blog', 'handleUpload'],
            ],

            [	//Test last slash extra still works
                'path' => '/blog/upload/',
                'routeName' => 'blogUpload',
                'callable' => ['BaseReality\\Controller\\Blog', 'handleUpload'],
            ],

            [	//Test last slash present as per route
                'path' => '/rss/',
                'routeName' => 'blogRSSFeed',
                'callable' => ['BaseReality\\Controller\\Blog', 'rssFeed'],
            ],

            [	//Test last slash optional
                'path' => '/rss',
                'routeName' => 'blogRSSFeed',
                'callable' => ['BaseReality\\Controller\\Blog', 'rssFeed'],
            ],

            [
                'path' => '/staticFile/someFile.gz',
                'routeName' => 'proxyStaticFile',
                'callable' => ['BaseReality\\Controller\\ProxyController', 'staticFile'],
                'routeParams' => [
                    ['filename', 'someFile.gz'],
                ]
            ],
            [
                'path' => '/staticFiles',
                'routeName' => 'StaticFiles',
                'callable' => ['BaseReality\\Controller\\Management\\StaticFile', 'display'],
            ],
            [
                'path' => '/image/1234/256/someImage.jpg',
                'routeName' => 'image',
                //'classMethod' => 'BaseReality\\Controller\\Management\\StaticFile::display',
                'callable' => ['BaseReality\\ImageController', 'showImage'],
                'routeParams' => [
                    ['imageID', 1234,],
                    ['size', 256],
                    ['filename', 'someImage.jpg'],
                    ['path', 'image']
                ]
            ],
            [
                'path' => '/image/1234/someImage.jpg',
                'routeName' => 'image',
                //'classMethod' => 'BaseReality\\Controller\\Management\\StaticFile::display',
                'callable' => ['BaseReality\\ImageController', 'showImage'],
                'routeParams' => [
                    ['imageID', 1234,],
                    ['size', null],
                    ['filename', 'someImage.jpg'],
                    ['path', 'image']
                ]
            ],
        );

        foreach ($testDataArray as $testData) {
            $request = clone $blankRequest;
            $request->setPath($testData['path']);
            $this->checkRouteValid($testData, $request);
        }
    }

    public function testException() {
        $this->setExpectedException('\Intahwebz\Routing\RouteMissingException');
        $requestDefine = $this->standardRequestDefine;
        $requestDefine['path'] = '/ThisDoesntExist';
        $blankRequest = new DefinedRequest($requestDefine);
        $this->router->matchRouteForRequest($blankRequest);
        $this->fail('An expected exception has not been raised.');
    }

    public function testGenerateURLForRoute() {

        $params = [
            'blogPostID' => 5,
            'title' => 'ABlogPost'
        ];

        $URL = $this->router->generateURLForRoute('blogPost', $params);

        $absoluteURL = $this->router->generateURLForRoute('blogPost', $params, true);

        $this->assertEquals('/blog/5/ABlogPost', $URL, "Relative URL not correct");
        $this->assertEquals('http://basereality.test/blog/5/ABlogPost', $absoluteURL, "Absolute URL not correct");
    }

    public function testGenerateURLForUnknownRoute() {
        $this->setExpectedException('\Intahwebz\Exception\UnsupportedOperationException');
        $this->router->generateURLForRoute('AnUnknownRoute', []);
    }

    public function testGetRoute() {
        $route = $this->router->getRoute('blogPost');
        $this->assertEquals('blogPost', $route->getName(), "Route not correct");
    }

    public function testGetRouteMissingException() {
        $this->setExpectedException('\Intahwebz\Routing\RouteMissingException');
        $route = $this->router->getRoute('nonExistantRoute');
        $this->assertNull($route, "Failed to not find non-existent route.");
    }


    public function testIPAccess() {

        $requestDefine = array(
            'hostName' => 'test.local',
            'scheme' => 'http',
            'requestParams' => array(),
            'port' => 80,
            'method' => 'GET',
            'path' => '/admin/'
            
        );

        $allowedRequest = new DefinedRequest(array_merge($requestDefine, ['clientIP' =>"10.0.2.2"]));
        $deniedRequest = new DefinedRequest(array_merge($requestDefine, ['clientIP' =>"8.8.8.8"]));

        $matchedRoute = $this->router->matchRouteForRequest($allowedRequest);
        
        $route = $matchedRoute->getRoute();
        
        $this->assertEquals($route->getName(), 'ipRestrict');

        $this->setExpectedException('\Intahwebz\Routing\RouteMissingException');
        $this->router->matchRouteForRequest($deniedRequest);

    }
    
    
    
}



