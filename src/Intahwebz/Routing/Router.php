<?php

namespace Intahwebz\Routing;

use Psr\Log\LoggerInterface;

use Intahwebz\ObjectCache;
use Intahwebz\Exception\UnsupportedOperationException;
use Intahwebz\Routable;
use Intahwebz\Route;
use Intahwebz\Request;

class Router implements \Intahwebz\Router {

    use \Intahwebz\SafeAccess;

    /**
     * @var $routesByName Route[]
     */
    var $routesByName = array();

    public $routingInfo;

    public $cacheRouteInfo = true;

    private $name;

    /**
     * @var ObjectCache
     */
    var $objectCache;


    var $domain;


    function	__construct(
        \Intahwebz\Domain $domain,
        ObjectCache $objectCache,
        $routeCollectionName,
        $pathToRouteInfo
    ){
        $this->domain = $domain;
        $this->objectCache = $objectCache;
        $this->init($routeCollectionName, $pathToRouteInfo);
    }

    /**
     * Initialise the router if it isn't already cached.
     * @param $routeCollectionName
     * @param $pathToRouteInfo
     */
    function init($routeCollectionName, $pathToRouteInfo) {
        $this->name = $routeCollectionName;

        $this->routesByName = $this->objectCache->get($routeCollectionName);

        if ($this->routesByName) {
            return;
        }

        if (is_array($pathToRouteInfo)) {
            $routingInfo = $pathToRouteInfo;
        }
        else {
            $routingInfo = require $pathToRouteInfo;
        }
        
        
        $this->initRouting($routingInfo);
        $this->objectCache->put($routeCollectionName, $this->routesByName, 60);
    }

    /**
     * Initializes the router with an array of routes.
     *
     * @param $routingInfoArray
     */
    function initRouting($routingInfoArray){
        foreach($routingInfoArray as $routingInfo){
            $name = $routingInfo['name'];
            $route = new \Intahwebz\Routing\Route($routingInfo);
            $this->routesByName[$name] = $route;
        }
    }

    /**
     * Find the most appropriate route, and route the request to it.
     *
     * @param $request
     * @return Route
     * @throws RouteMissingException
     */
    function getRouteForRequest(Request $request){
        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach ($this->routesByName as $name => $route) {
            $params = $route->matchRequestAndStoreParams($request);

            if($params !== false){
                return [$route, $params];
            }
        }

        throw new RouteMissingException("Could not route request with path: ".$request->getPath());
    }

    //TODO - this is still crap
    function generateURL(Routable $routable,  $absolute = false){
        $routeNameType = $routable->getRouteName().'_List';
        return $this->generateURLForRoute($routeNameType, $routable->getRouteParams(), $absolute);
    }

    /**
     * @param $routeName
     * @param array $parameters
     * @param bool $absolute
     * @return mixed|string
     * @throws \Intahwebz\Exception\UnsupportedOperationException
     */
    function generateURLForRoute($routeName, $parameters = array(), $absolute = false){
        foreach ($this->routesByName as $name => $route) {
            if($name == $routeName){
                return $route->generateURL($this->domain, $parameters, $absolute);
            }
        }

        throw new UnsupportedOperationException("Could not find route [$routeName] to generateURL for.");
    }

    /**
     * @param $routeName
     * @return Route
     * @throws RouteMissingException
     */
    function getRoute($routeName) {
        foreach ($this->routesByName as $name => $route) {
            if($name == $routeName){
                return $route;
            }
        }

        throw new RouteMissingException( "Could not find route '$routeName'");
    }
}

