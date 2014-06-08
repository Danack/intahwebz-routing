<?php

namespace Intahwebz\Routing;



class RouteVariable {

    public $name;

    public $requirement = NULL;

    public $default = NULL;

    public $optional = false;

    public function __construct($name, $nameWithWrapping, $default, $requirement, $optional){
        $this->name = $name;
        $this->optional = $optional;
        // $nameWithWrapping - unused?
        $this->default = $default;
        $this->requirement = $requirement;
    }

    /**
     * Converts requirements for variables into perl style regexes.
     * e.g. {name}, requirement => 'dan|ack' will only match those two possibilities.
     * @param bool $optionalPrefix
     * @return string
     */
    function getRegex($optionalPrefix = false) {
        if($this->requirement != NULL){
            $regex = "(?<".$this->name.">".$this->requirement.")";
        }
        else{
            $regex = "(?<".$this->name.">[^/]+)";
        }

        if ($this->optional == true || $this->default !== null) {
            if ($optionalPrefix) {
                $regex = '(?:'.$optionalPrefix.$regex.")";
            }

            $regex .= '?';
        }
        else {
            if ($optionalPrefix) {
                $regex = $optionalPrefix.$regex;
            }
        }

        return $regex;
    }
}


