<?php


namespace Intahwebz;


interface Router {

    /**
     * @param $routeName
     * @return Route
     */
    function getRoute($routeName);
    function generateURLForRoute(
        $routeName,
        $parameters = array(),
        \Intahwebz\Domain $domain = null,
        $absolute = false
    );

    /**
     * @param Request $request
     * @return \Intahwebz\MatchedRoute
     */
    function matchRouteForRequest(Request $request);
}

