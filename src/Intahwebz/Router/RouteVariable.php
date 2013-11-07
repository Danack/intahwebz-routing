<?php

namespace Intahwebz\Routing;



class RouteVariable{

    public $name;

    public $requirement = NULL;

    public $default = NULL;

    public function __construct($name){
        $this->name = $name;
    }

    function setRequirement($requirement) {
        $this->requirement = $requirement;
    }

    function  setDefault($default) {
        $this->default = $default;
    }

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

        //TODO - how do you set a default value of NULL
        if($this->default === NULL){
            return $regex;
        }
        else{
            return $regex."?";
        }
    }
}


