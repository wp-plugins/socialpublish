<?php

require_once __SOCIALPUBLISH_ROOT__ . '/domain/http/strategy/ASocialpublishHTTPStrategy.php';
require_once __SOCIALPUBLISH_ROOT__ . '/domain/http/SocialpublishHTTPException.php';

class SocialpublishHTTPFopenStrategy extends ASocialpublishHTTPStrategy
{
    protected static $instance;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new SocialpublishHTTPFopenStrategy();
        }

        return self::$instance;
    }

    public function isAvailable() {
        return false;
    }

    protected function send($method, $url, $parameters = null) {
        // TODO, but then again, fsockopen should always work...
        // using fopen can only cause errors if allow_url_fopen is false
    }
}

?>