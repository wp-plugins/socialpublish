<?php

class SocialpublishHTTP
{
    protected static $instance;

    protected $strategy;

    protected function __construct() {
        $this->strategy = null;
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new SocialpublishHTTP();
        }

        return self::$instance;
    }

    public function addStrategy(ASocialpublishHTTPStrategy $strategy) {
        if ($strategy->isAvailable()) {
            $this->strategy = $strategy;
        }
    }

    public function hasStrategy() {
        return $this->strategy !== null;
    }

    public function get($url, $parameters = null) {
        return $this->strategy->get($url, $parameters);
    }

    public function post($url, $parameters = null) {
        return $this->strategy->post($url, $parameters);
    }
}

?>