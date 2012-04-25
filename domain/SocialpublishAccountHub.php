<?php

class SocialpublishAccountHub
{
    protected $type;
    protected $id;
    protected $name;

    public function __construct($type, $id, $name) {
        $this->type = $type;
        $this->id   = $id;
        $this->name = $name;
    }

    public function getKey() {
        return $this->type . ':' . $this->id;
    }

    public function getType() {
        return $this->type;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }
}

?>