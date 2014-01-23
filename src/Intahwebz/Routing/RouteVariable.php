<?php

namespace Intahwebz\Routing;



class RouteVariable {

    public $name;

    public $requirement = NULL;

    public $default = NULL;

    private $optional = false;

    public function __construct($name, $nameWithWrapping, $default, $requirement, $optional){
        $this->name = $name;
        $this->optional = $optional;
        // $nameWithWrapping - unused?
        $this->default = $default;
        $this->requirement = $requirement;
    }

//    function setRequirement($requirement) {
//        $this->requirement = $requirement;
//    }
//
//    function  setDefault($default) {
//        $this->default = $default;
//    }

    /**
     * Converts requirements for variables into perl style regexes.
     * e.g. {name}, requirement => 'dan|ack' will only match those two possibilities.
     * @return string
     */
    function getRegex() {
        if($this->requirement != NULL){
            $regex = "(?<".$this->name.">".$this->requirement.")";
        }
        else{
            $regex = "(?<".$this->name.">[^/]+)";
        }

        if ($this->optional == true || $this->default !== null) {
            $regex .= "?";
        }

        return $regex;
    }
}


