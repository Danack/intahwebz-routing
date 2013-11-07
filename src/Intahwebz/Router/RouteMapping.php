<?php


namespace Intahwebz\Routing;

/**
 * Helper class that just holds data about the controllers for routes.
 */
class RouteMapping implements \Intahwebz\RouteMapping {

    private     $scheme;		//"BaseReality\\Controller",

    private     $className;		// "Images",

    private     $methodName;		// "show",

    public function	__construct($routeMappingInfo) {
        $this->scheme = $routeMappingInfo[0];
        $this->className = $routeMappingInfo[1];
        $this->methodName = $routeMappingInfo[2];
    }

    /**
     * Gets the class path for mapped controller
     *
     * @return string
     */
    function getClassPath(){
        $classPath = $this->className;

        if(mb_strlen($this->scheme) > 0){
            $classPath = $this->scheme."\\".$this->className;
        }

        return $classPath;
    }

    function __toString() {
        return $this->scheme.'\\'.$this->className.'::'.$this->methodName;
    }
    
    function getScheme() {
        return $this->scheme;
    }

    function getMethodName() {
        return $this->methodName;
    }
}

