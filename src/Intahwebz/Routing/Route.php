<?php

namespace Intahwebz\Routing;

use Intahwebz\Request;

use Intahwebz\Exception\UnsupportedOperationException;

class Route implements \Intahwebz\Route{

    use \Intahwebz\SafeAccess;

    public	$name;				// "pictures"

    public 	$pattern;			// "/pictures/{page}/{debugVar}",

    public	$regex;				// "#^/pictures/(?\d+)(?:/(?[^/]+))?$#s",

    public	$staticPrefix;		// "/pictures",

    public	$methodRequirement = null;

    public  $defaults = array();

    public  $resourceName = null;

    public  $privilegeName = null;

    private $template = null;

    public function getACLResourceName() {
        return $this->resourceName;
    }

    public function getTemplate() {
        return $this->template;
    }

    public function getACLPrivilegeName() {
        return $this->privilegeName;
    }

    /**
     * The parameters extracted from a request.
     * @var array
     */
    public	$routeParams = array();

    /**
     * @var  $variables RouteVariable[]
     */
    public 	$variables = array();

    /** @var RouteMapping */
    public $mapping;

    public function getRouteParams() {
        return $this->routeParams;
    }

    /**
     * @return \Intahwebz\RouteMapping
     */
    public function getMapping() {
        return $this->mapping;
    }


    function getName() {
        return $this->name;
    }

    public function getRouteParam($routeParamName) {
        if (array_key_exists($routeParamName, $this->routeParams)) {
            return $this->routeParams[$routeParamName];
        }
        return null;
    }

    /**
     * Makes a route out of an array of config data.
     *
     * @param $routeInfo
     */
    public function __construct($routeInfo) {
        $this->name = $routeInfo['name'];
        $this->pattern = $routeInfo['pattern'];

        if (array_key_exists('access', $routeInfo)) {
            $this->resourceName = $routeInfo['access'][0];
            if (isset($routeInfo['access'][1]) == true) {
                $this->privilegeName = $routeInfo['access'][1];
            }
        }

        if (array_key_exists('template', $routeInfo) == true) {
            $this->template = $routeInfo['template'];
        }

        if (array_key_exists('mapping', $routeInfo) == true) {
            $this->mapping = new RouteMapping($routeInfo['mapping']);
        }

        $firstBracketPosition = mb_strpos($this->pattern, '{');
        if($firstBracketPosition === false){
            //No variables
            $this->staticPrefix = $this->pattern;
        }
        else{
            $this->staticPrefix = mb_substr($this->pattern, 0, $firstBracketPosition);
        }

        //For paths other than the route path '/' allow the last '/' to be optional
        //If the string terminates there.
        if(mb_strlen($this->staticPrefix) > 1){
            if(mb_substr($this->staticPrefix, mb_strlen($this->staticPrefix)-1)   == '/'){
                $this->staticPrefix = mb_substr($this->staticPrefix, 0, mb_strlen($this->staticPrefix)-1);
            }

            if(mb_substr($this->pattern, mb_strlen($this->pattern)-1)   == '/'){
                $this->pattern = mb_substr($this->pattern, 0, mb_strlen($this->pattern)-1);
            }
        }

        $requirements = array();
        if(array_key_exists('requirements', $routeInfo) == true){
            $requirements = $routeInfo['requirements'];
        }

        if(array_key_exists('_method', $requirements) == true){
            $this->methodRequirement = $requirements['_method'];
        }

        if(array_key_exists('defaults', $routeInfo) == true){
            $this->defaults = $routeInfo['defaults'];
        }

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

                //  - /images/2 or /images/ or /images

                if(mb_substr($nextPart, mb_strlen($nextPart)-1)   == '/'){
                    //$nextPart = mb_substr($nextPart, 0, mb_strlen($nextPart)-1) ."(?:/)?";
                    $nextPart = mb_substr($nextPart, 0, mb_strlen($nextPart)-1) ."(?:/|$)";
                }

                $this->regex .= $nextPart;
            }

            $routerVariable = new RouteVariable($variableName, $variableNameWithWrapping);

//			$requiredParameter = false;
            if(array_key_exists($variableName, $this->defaults) == true){
                $routerVariable->setDefault($this->defaults[$variableName]);
            }
//			else{
//				$requiredParameter = true;
//			}

            if(array_key_exists($variableName, $requirements) == true){
                $routerVariable->setRequirement($requirements[$variableName]);
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

    function matchRequestAndStoreParams(Request $request) {
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

        $params = array();

        foreach($this->variables as $routeVariable){
            if(array_key_exists($routeVariable->name, $matches) == true){
                $params[$routeVariable->name] = $matches[$routeVariable->name];
            }
            else if($routeVariable->default != null){
                $params[$routeVariable->name] = $routeVariable->default;
            }

            //TODO replace this with the preg_match on the whole route.
//			if ($routeVariable->matchesRequirement($params[$routeVariable->name]) == false) {
//				return false;
//			}
        }

        //TODO this is bad state
        $this->routeParams = $params;

        return true;
    }


    function getMergedParameters(Request $request) {
        //later value for that key will overwrite the previous one, so higher priority values come later
        $mergedParameters = array();
        $mergedParameters = array_merge($mergedParameters, $this->defaults);
        $mergedParameters = array_merge($mergedParameters, $this->routeParams);
        $mergedParameters = array_merge($mergedParameters, $request->getRequestParams());

        return $mergedParameters;
    }

    /**
     * Generate a list of arguments to be passed to a controller, from the route
     * and request.
     *
     * TODO - delete this? It's redundant and slightly shite.
     *
     * @param Request $request
     * @throws \RuntimeException
     * @return array
     */
    function mapParametersToFunctionArguments(Request $request) {

        $reflector = new \ReflectionMethod($this->mapping->getClassPath(), $this->mapping->getMethodName());

        $parameters = $reflector->getParameters();

        $arguments = array();

        //later value for that key will overwrite the previous one, so higher priority values come later
        $mergedParameters = array();
        $mergedParameters = array_merge($mergedParameters, $this->defaults);
        $mergedParameters = array_merge($mergedParameters, $this->routeParams);
        $mergedParameters = array_merge($mergedParameters, $request->getRequestParams());

        foreach ($parameters as $param) {
            //If we have it as a parameter from the route/request
            if(array_key_exists($param->name, $mergedParameters) == true){
                $arguments[] = $mergedParameters[$param->name];
            }
            //If function wants request, pass it in
            elseif ($param->getClass() && $param->getClass()->isInstance($request)) {
                $arguments[] = $request;
            }
            //If default available, set it
            elseif ($param->isDefaultValueAvailable()) {
                $arguments[] = $param->getDefaultValue();
            }
            else {
                throw new \RuntimeException("Controller [".$this->mapping->getClassPath()."], method [".$this->mapping->getMethodName()."] requires that you provide a value for the [".$param->name."] argument (because there is no default value or because there is a non optional argument after this one).");
            }
        }

        return $arguments;
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