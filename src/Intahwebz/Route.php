<?php

namespace Intahwebz;

interface Route {

    public function getName();

    public function generateURL($parameters, \Intahwebz\Domain $domain = null,  $absolute = false);
    public function matchRequest(Request $request);

    /**
     * Gets on of the 'extra' values associated with this route.
     * @param $key
     * @return mixed
     */
    function get($key);

    function getDefaults();
}

