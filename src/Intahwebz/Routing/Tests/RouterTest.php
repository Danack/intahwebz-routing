<?php


namespace Intahwebz\Tests\Routing;

use Intahwebz\Cache\NullObjectCache;
use Intahwebz\Logger\NullLogger;

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


        $domain = new \Intahwebz\DomainExample($blankRequest);

        $this->router = new Router($domain,
            $objectCache, 
            new NullLogger(),
            "RouteUnitTests", 
            dirname(__DIR__)."/Tests/RouteTestData.php"
        );
    }

    protected function tearDown(){
    }


    public function textRoute($expection, Request $request)
    {
        $route = $this->router->getRouteForRequest($request);

        if (array_key_exists('routeName', $expection) == true) {
            $this->assertEquals($expection['routeName'], $route->getName());
        }

        if (array_key_exists('classMethod', $expection) == true) {
            $mapping = $route->getMapping();
            
            $classMethod = $mapping->getClassPath().'::'.$mapping->getMethodName();
            $this->assertEquals($expection['classMethod'], $classMethod);
        }

        if (array_key_exists('routeParams', $expection) == true) {
            foreach ($expection['routeParams'] as $routeParam) {
                $routeParamName = $routeParam[0];
                $routeParamValue = $routeParam[1];
                $this->assertEquals($routeParamValue, $route->getRouteParam($routeParamName), "Route param doesn't match expected value:");
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
                'classMethod' => 'BaseReality\\Controller\\Blog::displayIndex',
                'path' => '/',
            ],

            [	//Extract variable.
                'path' => '/blog/5/BlogTitle',
                'routeName' => 'blogPost',
                'classMethod' => 'BaseReality\\Controller\\Blog::display',
                'routeParams' => [
                    ['blogPostID', 5,],
                    ['title', 'BlogTitle']
                ]
            ],

            [	//extract two variables.
                'path' => '/blog/5/BlogTitle.text',
                'routeName' => 'blogPost',
                'classMethod' => 'BaseReality\\Controller\\Blog::display',
                'routeParams' => [
                     ['blogPostID', 5,],
                     ['title', 'BlogTitle'],
                     ['format', 'text']
                 ]
            ],

            [	//Test last slash not present
                'path' => '/blog/upload',
                'routeName' => 'blogUpload',
                'classMethod' => 'BaseReality\\Controller\\Blog::handleUpload',
            ],

            [	//Test last slash extra still works
                'path' => '/blog/upload/',
                'routeName' => 'blogUpload',
                'classMethod' => 'BaseReality\\Controller\\Blog::handleUpload',
            ],

            [	//Test last slash present as per route
                'path' => '/rss/',
                'routeName' => 'blogRSSFeed',
                'classMethod' => 'BaseReality\\Controller\\Blog::rssFeed',
            ],

            [	//Test last slash optional
                'path' => '/rss',
                'routeName' => 'blogRSSFeed',
                'classMethod' => 'BaseReality\\Controller\\Blog::rssFeed',
            ],

            [
                'path' => '/staticFile/someFile.gz',
                'routeName' => 'proxyStaticFile',
                'classMethod' => 'BaseReality\\Controller\\ProxyController::staticFile',
                'routeParams' => [
                    ['filename', 'someFile.gz'],
                ]
            ],
            [
                'path' => '/staticFiles',
                'routeName' => 'StaticFiles',
                'classMethod' => 'BaseReality\\Controller\\Management\\StaticFile::display',
            ],
        );

        foreach ($testDataArray as $testData) {
            $request = clone $blankRequest;
            $request->setPath($testData['path']);

            $this->textRoute($testData, $request);
        }
    }

    public function testException() {
        $this->setExpectedException(\Intahwebz\Router\RouteMissingException::class);
        $requestDefine = $this->standardRequestDefine;
        $requestDefine['path'] = '/ThisDoesntExist';
        $blankRequest = new DefinedRequest($requestDefine);
        $this->router->getRouteForRequest($blankRequest);
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
        $this->setExpectedException(\Intahwebz\Exception\UnsupportedOperationException::class);
        $this->router->generateURLForRoute('AnUnknownRoute', []);
    }

    public function testGetRoute() {
        $route = $this->router->getRoute('blogPost');
        $this->assertEquals('blogPost', $route->getName(), "Route not correct");
    }

    public function testGetRouteMissingException() {
        $this->setExpectedException(\Intahwebz\Router\RouteMissingException::class);
        $route = $this->router->getRoute('nonExistantRoute');
        $this->assertNull($route, "Failed to not find non-existent route.");
    }


}



