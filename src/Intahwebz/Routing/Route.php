<?php

namespace Intahwebz\Routing;

use Intahwebz\Request;

use Intahwebz\Exception\UnsupportedOperationException;



class Route implements \Intahwebz\Route {

    use \Intahwebz\SafeAccess;

    private $name;				// "pictures"

    private $pattern;			// "/pictures/{page}/{debugVar}",

    private $regex;				// "#^/pictures/(?\d+)(?:/(?[^/]+))?$#s",

    private $staticPrefix;		// "/pictures",

    private $methodRequirement = null;

    private $defaults = array();

    private $optionalInfo = array();

    private $fnCheck = array();

    private $extra = array();


    /**
     * @var $variables RouteVariable[]
     */
    public $variables = array();

    /** @var callable */
    public $callable;

    private $requirements = array();

    public function getDefaults() {
        return $this->defaults;
    }
    
    /**
     * @return callable
     */
    public function getCallable() {
        return $this->callable;
    }

    function getName() {
        return $this->name;
    }

    /**
     * Makes a route out of an array of config data.
     *
     * @param $routeInfo
     */
    public function __construct($routeInfo) {
        $this->name = $routeInfo['name'];
        $this->pattern = $routeInfo['pattern'];

        
        foreach ($routeInfo as $key => $value) {
         
            switch($key) {

                case('access') :{
                    $this->resourceName = $routeInfo['access'][0];
                    if (isset($routeInfo['access'][1]) == true) {
                        $this->privilegeName = $routeInfo['access'][1];
                    }
                    break;
                }
                
                case ('requirements'): {
                    $this->requirements = $value;
                    break;
                }
                case ('_method'): {
                    $this->methodRequirement = $value;
                    break;
                }
                case ('defaults'): {
                    $this->defaults = $value;
                    break;
                }
                case ('callable'): {
                    $this->callable = $routeInfo['callable'];
                    break;
                }
                case ('fnCheck'): {
                    $this->fnCheck = $routeInfo['fnCheck'];
                    break;
                }

                case('name'):
                case('pattern'):{ break;}
                    
                case('optional'): {
                    $this->optionalInfo = $routeInfo['optional'];
                    break;
                }

                default:{
                    $this->extra[$key] = $value;
                    break;
                }
            }
        }

        $this->calculateStaticPrefix();

        //For paths other than the route path '/' allow the last '/' to be optional
        //If the string terminates there.
        if(mb_strlen($this->staticPrefix) > 1) {
            if(mb_substr($this->staticPrefix, mb_strlen($this->staticPrefix)-1) == '/'){
                $this->staticPrefix = mb_substr($this->staticPrefix, 0, mb_strlen($this->staticPrefix)-1);
            }

            if(mb_substr($this->pattern, mb_strlen($this->pattern)-1) == '/'){
                $this->pattern = mb_substr($this->pattern, 0, mb_strlen($this->pattern)-1);
            }
        }

        $this->buildRegex();
    }
    
    function get($key) {
        if (array_key_exists($key, $this->extra) == true) {
            return $this->extra[$key];
        }
        return null;
    }

    function calculateStaticPrefix() {
        $firstBracketPosition = mb_strpos($this->pattern, '{');
        if($firstBracketPosition === false){
            //No variables
            $this->staticPrefix = $this->pattern;
        }
        else{
            $this->staticPrefix = mb_substr($this->pattern, 0, $firstBracketPosition);
        }
    }

    function buildRegex() {
        $matches = array();

        // We need the position of the matches to allow us to rebuild the pattern string
        // as the regex string
        preg_match_all('#\{(\w+)\}#', $this->pattern, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        $currentPosition = 0;
        $this->regex = '';

        foreach ($matches as $match) {
            $variableNameWithWrapping = $match[0][0]; // '/{page}'
            $variableNameWithWrappingPosition = $match[0][1]; // '/{page}'

            $variableName = $match[1][0];
            //$variableNamePosition = $match[1][1];

            if($currentPosition < $variableNameWithWrappingPosition){
                $nextPart = mb_substr($this->pattern, $currentPosition, $variableNameWithWrappingPosition - $currentPosition);
                $this->regex .= $nextPart;
            }

            $optional = false;
            
            if (array_key_exists($variableName, $this->optionalInfo) == true) {
                $optional = $this->optionalInfo[$variableName];
            }

            $default = null;
            if(array_key_exists($variableName, $this->defaults) == true){
                $default = $this->defaults[$variableName];
            }

            $requirement = null;
            if(array_key_exists($variableName, $this->requirements) == true){
                $requirement = $this->requirements[$variableName];
            }

            $routerVariable = new RouteVariable(
                $variableName,
                $variableNameWithWrapping,
                $default,
                $requirement,
                $optional
            );

            if ($optional) {
                $lastPos = strlen($this->regex) - 1;
                $lastSlash = strrpos($this->regex, '/');

                if ($lastSlash == $lastPos) {
                    $this->regex = substr($this->regex, 0, -1)."(?:/)?";
                }
            }

            $this->regex .= $routerVariable->getRegex();
            $this->variables[] = $routerVariable;

            $currentPosition = $variableNameWithWrappingPosition + mb_strlen($variableNameWithWrapping);
        }

        $this->regex .= mb_substr($this->pattern, $currentPosition);

        //let there be an optional last slash
        $this->regex .= '(/)?';

        $REGEX_DELIMITER = '#';

        $this->regex = $REGEX_DELIMITER.'^'.$this->regex.'$'.$REGEX_DELIMITER;
    }

    /**
     * Test that a request meets the path and other requirements for a route.
     * Returns true if the route wash matched.
     *
     * @param Request $request
     * @return array|bool
     */

    function matchRequest(Request $request) {
        $requestPath = $request->getPath();

        if (mb_strpos($requestPath, $this->staticPrefix) !== 0) {
            return false;
        }

        if ($this->methodRequirement != null){
            if(mb_strcasecmp($this->methodRequirement, $request->getMethod()) != 0){
                return false;
            }
        }

        $result = preg_match($this->regex, $requestPath, $matches);

        if ($result == false) {
            return false;
        }

        foreach ($this->fnCheck as $fnCheck) {
            $result = $fnCheck($request);
            if (!$result) {
                return false;
            }
        }

        //Route has matched
        $params = array();

        foreach($this->variables as $routeVariable){
            if(array_key_exists($routeVariable->name, $matches) == true){
                $params[$routeVariable->name] = $matches[$routeVariable->name];
            }
            else if($routeVariable->default != null){
                $params[$routeVariable->name] = $routeVariable->default;
            }
        }

        return $params;
    }

    function getDefaultParams() {
        return $this->defaults;
    }

    private function getRequiredPathComponents($params) {

        $notBracketMatch = "\{\w+\}";
        $notParenthesis = "\(.*\)\?";
        $anythingElse = "[\w\d_\/\\\\]+";

        preg_match_all("#(($notBracketMatch)|($notParenthesis)|($anythingElse))#", $this->pattern, $matches, PREG_OFFSET_CAPTURE|PREG_PATTERN_ORDER);

        $parts = $matches[0];

        $requiredString = false;
        $requiredOffset = false;

        foreach ($parts as $part) {

            $string = $part[0];
            $offset = $part[1];

            $firstChar = mb_substr($string, 0, 1);

            $required = false;

            switch($firstChar){

                case('{'):{
                    $pattern = str_replace(array('{', '}'), '', $string);
                    if (array_key_exists($pattern, $params) == true) {
                        $required = true;
                    }
                    break;
                }

                case('('):{
                    break;
                }

                default:{
                    $required = true;
                }
            }

            if ($required == true) {
                $requiredString = $string;
                $requiredOffset = $offset;
            }
        }

        $actualRequired = false;

        if ($requiredOffset != false) {
            $actualRequired = $requiredOffset + mb_strlen($requiredString);
        }

        return $actualRequired;
    }

    /**
     * Generate a URL for a route with the given parameters. Throws an exception if the route can't be generated
     * e.g. due to missing parameters
     *
     * @param \Intahwebz\Domain $domain
     * @param $parameters
     * @param bool $absolute
     * @throws \Intahwebz\Exception\UnsupportedOperationException
     * @return mixed|string
     */
    function generateURL(\Intahwebz\Domain $domain, $parameters, $absolute = false) {
        $search = array();
        $replace = array();

        //TODO - this doens't pickup provided non-default parameters.
        $patternToGenerate = $this->pattern;

        $actualRequired = $this->getRequiredPathComponents($parameters);
        if ($actualRequired !== false) {
            $patternToGenerate = mb_substr($patternToGenerate, 0, $actualRequired);
        }

        foreach($this->variables as $routeVariable){
            $variableName = $routeVariable->name;
            if(array_key_exists($variableName, $parameters) == true){
                $search[] = '{'.$variableName.'}';
                $replace[] = $parameters[$variableName];
                unset($parameters[$variableName]);
            }
            else if($routeVariable->default !== null){
                $search[] = '{'.$variableName.'}';
                $replace[] = $routeVariable->default;
            }
            else{
                throw new UnsupportedOperationException("Cannot generate route '".$this->name."'. Parameter '".$routeVariable->name."' is not set and has no default.");
            }
        }

        $url = str_replace($search, $replace, $patternToGenerate);

        if ($url === '') {
            $url = '/';
        }

        // add a query string if needed
        if(count($parameters) > 0){
            $query = http_build_query($parameters);
            $url .= '?'.$query;
        }

        if($absolute == true){
            //TODO Global function - eww.
            $url = $domain->getURLForCurrentDomain($url);
        }

        return $url;
    }
}
