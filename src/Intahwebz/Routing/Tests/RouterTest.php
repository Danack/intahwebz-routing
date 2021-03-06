<?php


namespace Intahwebz\Tests\Routing;

use Intahwebz\Cache\NullObjectCache;

use Intahwebz\Domain;
use Intahwebz\Request;
use Intahwebz\Routing\Router;
use Intahwebz\Routing\DefinedRequest;


class DomainTest implements Domain {


    function getTestDomainInfo() {

        $domainInfo = new \Intahwebz\DomainInfo(
            'localhost.test',
            'localhost.test',
            'www'.'localhost.test',
            'http',
            true,
            '/'
        );

        return $domainInfo;
    }

    function getContentDomain($contentID){
        return $this->getTestDomainInfo();
    }

    /**
     * @return \Intahwebz\DomainInfo
     */
    function getDomainInfo() {
        return $this->getTestDomainInfo();
    }

    function getURLForCurrentDomain($path, $secure = FALSE) {
        return "http://localhost.test".$path;
    }
    
}


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


    static function setUpBeforeClass() {
    }


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

        $this->router = new Router(
            $objectCache,
            "RouteUnitTests", 
            dirname(__DIR__)."/Tests/RouteTestData.php"
        );
    }

    protected function tearDown(){
    }


    /**
     * @dataProvider provideRoutesValid
     */
    public function testRoutesValid(Request $request, $expection)
    {
        $matchedRoute = $this->router->matchRouteForRequest($request);

        $route = $matchedRoute->getRoute();

        $this->assertNotNull($route, "Failed to match route.");

        if (array_key_exists('routeName', $expection) == true) {
            $this->assertSame($expection['routeName'], $route->getName());
        }

        if (array_key_exists('classMethod', $expection) == true) {
           $this->assertTrue(false, "This is never meant to be used anymore.");
        }

        if (array_key_exists('callable', $expection) == true) {
            $this->assertSame($expection['callable'], $route->get('callable'));
        }

        if (array_key_exists('routeParams', $expection) == true) {
            foreach ($expection['routeParams'] as $routeParam) {
                $routeParamName = $routeParam[0];
                $routeParamValue = $routeParam[1];
                $mergedParams = $matchedRoute->getMergedParameters();
                $this->assertArrayHasKey($routeParamName, $mergedParams);

                $this->assertSame(
                    $routeParamValue, 
                    $mergedParams[$routeParamName], 
                    "Route param doesn't match expected value:"
                );
            }
        }
    }

    public function provideRoutesValid()
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
                    ['blogPostID', '5',],
                    ['title', 'BlogTitle']
                ]
            ],

            [	//extract two variables.
                'path' => '/blog/5/BlogTitle.text',
                'routeName' => 'blogPost',
                'callable' => ['BaseReality\\Controller\\Blog', 'display'],
                'routeParams' => [
                     ['blogPostID', '5',],
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
                    ['imageID', '1234',],
                    ['size', '256'],
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
                    ['imageID', '1234',],
                    ['size', null],
                    ['filename', 'someImage.jpg'],
                    ['path', 'image']
                ]
            ],
            [	//Test last slash optional
                'path' => '/pictures',
                'routeName' => 'pictures',
                'callable' => array('ImageClass', 'show', ),
            ],
            [	//Test last slash optional
                'path' => '/css/cssInclude/15342731330643,jQuery%2Fjquery-ui-1.10.0.custom,css%2Fbasereality,colorPicker',
                'routeName' => 'cssInclude'
            ],
        );

        $dataList = array();
        
        foreach ($testDataArray as $testData) {
            $data = array();

            $request = clone $blankRequest;
            $request->setPath($testData['path']);
            $data[0] = $request;
            $data[1] = $testData;

            $dataList[] = $data;
        }

        return $dataList;
    }

    public function testException() {
        $this->setExpectedException('\Intahwebz\Routing\RouteMissingException');
        $requestDefine = $this->standardRequestDefine;
        $requestDefine['path'] = '/ThisDoesntExist';
        $blankRequest = new DefinedRequest($requestDefine);
        $matchedRoute = $this->router->matchRouteForRequest($blankRequest);
        $this->fail('An expected exception has not been raised.');
    }

    public function testGenerateURLForRoute() {

        $params = [
            'blogPostID' => 5,
            'title' => 'ABlogPost'
        ];

        $URL = $this->router->generateURLForRoute('blogPost', $params);
        
        
        $domain = new DomainTest();

        $absoluteURL = $this->router->generateURLForRoute('blogPost', $params, $domain, true);

        $this->assertSame('/blog/5/ABlogPost', $URL, "Relative URL not correct");
        $this->assertSame('http://localhost.test/blog/5/ABlogPost', $absoluteURL, "Absolute URL not correct");
    }

    public function testGenerateURLForUnknownRoute() {
        $this->setExpectedException('\Intahwebz\Exception\UnsupportedOperationException');
        $this->router->generateURLForRoute('AnUnknownRoute', []);
    }

    public function testGetRoute() {
        $route = $this->router->getRoute('blogPost');
        $this->assertSame('blogPost', $route->getName(), "Route not correct");
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
        
        $this->assertSame($route->getName(), 'ipRestrict');

        $this->setExpectedException('\Intahwebz\Routing\RouteMissingException');
        $this->router->matchRouteForRequest($deniedRequest);
        
        throw new \Intahwebz\Routing\RouteMissingException("Could not find route.");
    }
}



