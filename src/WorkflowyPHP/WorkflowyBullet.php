<?php namespace WorkflowyPHP;

class WorkflowyBullet
{

    private $id;
    private $name;
    private $complete;
    private $description;
    private $children;

    public function __construct($id, $name, $complete, $description, $children)
    {
        $this->id          = $id;
        $this->name        = $name;
        $this->complete    = $complete;
        $this->description = $description;
        $this->children    = $children;
    }

}
