<?php

class SocialpublishAccountHub
{
    protected $type;
    protected $name;

    public function __construct($type, $name) {
        $this->type = $type;
        $this->name = $name;
    }

    public function getKey() {
        return $this->type . ':' . $this->name;
    }

    public function getType() {
        return $this->type;
    }

    public function getName() {
        return $this->name;
    }
}

?>