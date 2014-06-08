<?php


namespace Intahwebz;


class MatchedRoute {

    /**
     * @var \Intahwebz\Route
     */
    private $route;
    
    private $params;
    
    private $request;
    
    function __construct(\Intahwebz\Request $request, \Intahwebz\Route $route, array $params) {
        $this->params = $params;
        $this->route = $route;
        $this->request = $request;
    }

    /**
     * @return array
     */
    function getParams() {
        return $this->params;
    }

    /**
     * @return \Intahwebz\Route
     */
    function getRoute() {
        return $this->route;
    }

    function getMergedParameters($prefix = '') {
        //later value for that key will overwrite the previous one, so higher priority values come later
        $mergedParameters = array();
        $mergedParameters = array_merge($mergedParameters, $this->route->getDefaults());
        //$mergedParameters = array_merge($mergedParameters, $this->routeParams);
        $mergedParameters = array_merge($mergedParameters, $this->params);
        $mergedParameters = array_merge($mergedParameters, $this->request->getRequestParams());

        if ($prefix != '') {
            $injectableRouteParams = array();
            foreach ($mergedParameters as $key => $value) {
                $injectableRouteParams[':'.$key] = $value;
            }
            $mergedParameters = $injectableRouteParams;
        }

        return $mergedParameters;
    }
}

 