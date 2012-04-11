<?php

class SocialpublishAccount
{
    protected $accessToken;
    protected $hubs;

    public function __construct($accessToken) {
        $this->accessToken = $accessToken;
        $this->hubs = array();
    }

    public function getAccessToken() {
        return $this->accessToken;
    }

    public function setHubs($hubs) {
        $this->hubs = $hubs;
    }

    public function getHubs() {
        return $this->hubs;
    }
}

?>