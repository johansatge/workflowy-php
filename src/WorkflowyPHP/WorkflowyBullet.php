<?php namespace WorkflowyPHP;


class WorkflowyBullet
{

    private $id;
    private $name;
    private $description;
    private $children;

    public function __construct($id, $name, $description, $children)
    {
        $this->id          = $id;
        $this->name        = $name;
        $this->description = $description;
        $this->children    = $children;
    }

}
