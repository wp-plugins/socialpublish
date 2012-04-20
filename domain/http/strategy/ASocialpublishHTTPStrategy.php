<?php

abstract class ASocialpublishHTTPStrategy
{
    protected static $instance;

    protected function __construct() {}

    public abstract function isAvailable();

    public abstract static function getInstance();

    public function get($url, $parameters = null) {
        return $this->send('GET', $url, $parameters);
    }

    public function post($url, $parameters = null) {
        return $this->send('POST', $url, $parameters);
    }

    protected abstract function send($method, $url, $parameters = null);
}

?>