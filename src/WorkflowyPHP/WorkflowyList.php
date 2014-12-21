<?php namespace WorkflowyPHP;

class WorkflowyList
{

    private $id;
    private $name;
    private $complete;
    private $description;
    private $children;

    /**
     * Builds a recursive list
     * @param string $id
     * @param string $name
     * @param bool   $complete
     * @param string $description
     * @param array  $children
     */
    public function __construct($id, $name, $complete, $description, $children)
    {
        $this->id          = $id;
        $this->name        = $name;
        $this->complete    = $complete;
        $this->description = $description;
        $this->children    = $children;
    }

    /**
     * Search recursively if the list has the requested name
     * @param string $name
     * @return bool|WorkflowyList
     */
    public function searchByName($name)
    {
        return $this->search('/' . preg_quote($name, '/') . '/i', 'name');
    }

    /**
     * Search recursively if the list has the requested ID
     * @param string $id
     * @return bool|WorkflowyList
     */
    public function searchByID($id)
    {
        return $this->search('/^' . preg_quote($id, '/') . '$/', 'id');
    }

    public function create($name, $description, $priority)
    {
        // @todo
    }

    public function move($parent_list, $priority)
    {
        // @todo
    }

    public function setCompleted()
    {
        // @todo
    }

    public function delete()
    {
        // @todo
    }

    public function edit($list, $name, $description)
    {
        // @todo
    }

    public function getOPML()
    {
        // @todo
    }

    /**
     * Recursively check if the given regexp matches the requested field
     * If so, returns the list; otherwise returns false
     * @param string $regexp
     * @param string $field
     * @return WorkflowyList|bool
     */
    private function search($regexp, $field)
    {
        if (preg_match($regexp, $this->{$field}))
        {
            return $this;
        }
        foreach ($this->children as $child)
        {
            $match = $child->search($regexp, $field);
            if ($match !== false)
            {
                return $match;
            }
        }
        return false;
    }

}
