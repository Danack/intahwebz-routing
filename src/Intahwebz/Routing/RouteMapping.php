<?php


namespace Intahwebz\Routing;

/**
 * Helper class that just holds data about the controllers for routes.
 */
class RouteMapping implements \Intahwebz\RouteMapping {

    private     $className;

    private     $methodName;

    function __construct($routeMappingInfo) {

        if (count($routeMappingInfo) == 3) {
            $this->className = $routeMappingInfo[0].'\\'.$routeMappingInfo[1];
            $this->methodName = $routeMappingInfo[2];
        }
        else{
            $this->className = $routeMappingInfo[0];
            $this->methodName = $routeMappingInfo[1];
        }
    }

    /**
     * Gets the class path for mapped controller
     *
     * @return string
     */
    function getClassPath() {
        $classPath = $this->className;

        return $classPath;
    }

    function __toString() {
        return $this->className.'::'.$this->methodName;
    }

    function getMethodName() {
        return $this->methodName;
    }
}

