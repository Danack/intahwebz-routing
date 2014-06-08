<?php

namespace Intahwebz\Routing;

use Intahwebz\ObjectCache;
use Intahwebz\Exception\UnsupportedOperationException;
use Intahwebz\Route;
use Intahwebz\Request;
use Intahwebz\MatchedRoute;

class Router implements \Intahwebz\Router {

    use \Intahwebz\SafeAccess;
    use \Intahwebz\Cache\KeyName;

    /**
     * @var $routesByName Route[]
     */
    var $routesByName = array();

    public $routingInfo;

    public $cacheRouteInfo = true;

    /**
     * @var ObjectCache
     */
    var $objectCache;

    function __construct(
        ObjectCache $objectCache,
        $routeCollectionName,
        $pathToRouteInfo
    ){
        $this->objectCache = $objectCache;
        $this->init($routeCollectionName, $pathToRouteInfo);
    }

    /**
     * Initialise the router if it isn't already cached.
     * @param $routeCollectionName
     * @param $pathToRouteInfo
     */
    function init($routeCollectionName, $pathToRouteInfo) {

        $keyname = $this->getClassKey($routeCollectionName);
        $this->routesByName = $this->objectCache->get($keyname);

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
        $this->objectCache->put($keyname, $this->routesByName, 60);
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
     * @return MatchedRoute
     */
    function matchRouteForRequest(Request $request){
        /** @noinspection PhpUnusedLocalVariableInspection */
        foreach ($this->routesByName as $name => $route) {
            $params = $route->matchRequest($request);

            if($params !== false){
                return new MatchedRoute($request, $route, $params);
            }
        }

        throw new \Intahwebz\Routing\RouteMissingException("Failed to match request to route.");
    }

    /**
     * @param $routeName
     * @param \Intahwebz\Domain $domain
     * @param array $parameters
     * @param bool $absolute
     * @throws \Intahwebz\Exception\UnsupportedOperationException
     * @return mixed|string
     */
    function generateURLForRoute(
        $routeName,
        $parameters = array(),
        \Intahwebz\Domain $domain = null,
        $absolute = false
    ) {
        foreach ($this->routesByName as $name => $route) {
            if($name == $routeName){
                return $route->generateURL($parameters, $domain, $absolute);
            }
        }

        throw new UnsupportedOperationException("Could not find route [$routeName] to generateURL for.");
    }

    /**
     * @param $routeName
     * @return Route
     * @throws UnsupportedOperationException
     */
    function getRoute($routeName) {
        foreach ($this->routesByName as $name => $route) {
            if($name == $routeName){
                return $route;
            }
        }

        throw new \Intahwebz\Routing\RouteMissingException("Could not find route [$routeName] to generateURL for.");
    }

    /**
     * @param $pattern
     */
    function addRoute($pattern) {
        $routingInfo = array();
        $routingInfo['pattern'] = $pattern;
        
        $route = new \Intahwebz\Routing\Route($routingInfo);
        $this->routesByName[] = $route;
    }   
}

