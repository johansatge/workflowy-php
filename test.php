<?php

$raw_item = array(
    'name'     => 'Nom 1 niveau 0',
    'children' => array(
        array(
            'name'     => 'Nom 1 niveau 1',
            'children' => array(
                array(
                    'name'     => 'Nom 1 niveau 2',
                    'children' => array(
                        array(
                            'name'     => 'Nom 1 niveau 3',
                            'children' => array()
                        )
                    )
                ),
                array(
                    'name'     => 'Nom 2 niveau 2',
                    'children' => array()
                )
            )
        ),
        array(
            'name'     => 'Nom 2 niveau 1',
            'children' => array()
        ),
        array(
            'name'     => 'Nom 3 niveau 1',
            'children' => array()
        )
    )
);

$object = parse_raw_item($raw_item);

var_dump($object->children[0]->children[1]); //getParent());
var_dump($object->children[0]->children[1]->getParent());

function parse_raw_item($raw_item)
{
    $sub_objects = array();
    foreach ($raw_item['children'] as $sub_raw_item)
    {
        $sub_objects[] = parse_raw_item($sub_raw_item);
    }
    return new Foo($raw_item['name'], $sub_objects);
}

class Foo
{

    private $relations;

    public $children;

    public function __construct($name, $children)
    {
        $this->name     = $name;
        $this->children = $children;
    }

    public function getParent()
    {
        return 'comment je choppe l\'objet parent ?';
    }

}