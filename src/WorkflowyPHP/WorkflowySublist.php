<?php

/* WorkflowyPHP - https://github.com/johansatge/workflowy-php */

namespace WorkflowyPHP;

class WorkflowySublist
{

    private $id;
    private $name;
    private $complete;
    private $description;
    private $sublists;

    private $list;
    private $transport;

    /**
     * Builds a recursive list
     * This class is used to read a list content or update it
     * /!\ Keep in mind than on update, the list content will not be impacted
     *     For instance, when modyfing the description, the getDescription method will still return the old value
     * @param string $id
     * @param string $name
     * @param string $description
     * @param bool $complete
     * @param array $sublists
     * @param WorkflowyList $list
     * @param WorkflowyTransport $transport
     * @throws WorkflowyException
     */
    public function __construct($id, $name, $description, $complete, $sublists, $list, $transport)
    {
        $this->id          = is_string($id) ? $id : '';
        $this->name        = is_string($name) ? $name : '';
        $this->description = is_string($description) ? $description : '';
        $this->complete    = $complete ? true : false;
        $this->sublists    = array();
        if (is_array($sublists))
        {
            foreach ($sublists as $sublist)
            {
                if (!is_a($sublist, '\WorkflowyPHP\WorkflowySublist'))
                {
                    throw new WorkflowyException('Sublists must be WorkflowySublist instances');
                }
                $this->sublists[] = $sublist;
            }
        }
        if (!is_a($list, '\WorkflowyPHP\WorkflowyList'))
        {
            throw new WorkflowyException('List must be a WorkflowyList instance');
        }
        $this->list = $list;
        if (!is_a($transport, '\WorkflowyPHP\WorkflowyTransport'))
        {
            throw new WorkflowyException('Transport must be a WorkflowyTransport instance');
        }
        $this->transport = $transport;
    }

    /**
     * Returns the list ID
     * @return string
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * Returns the list name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the list description
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the parent of the list
     * @return bool|WorkflowySublist
     */
    public function getParent()
    {
        return $this->list->getSublistParent($this->id);
    }

    /**
     * Returns the completion status of the list
     * @return bool
     */
    public function isComplete()
    {
        return $this->complete;
    }

    /**
     * Returns the OPML view
     * @return string
     */
    public function getOPML()
    {
        $exporter = new WorkflowyOPML($this);
        return $exporter->export();
    }

    /**
     * Returns an array of sublists
     * @return array
     */
    public function getSublists()
    {
        return $this->sublists;
    }

    /**
     * Search recursively if the list name matches the given expression
     * Returns the first match, or an array of matches if the $get_all parameter is set to true
     * @param string $expression
     * @param bool $get_all
     * @throws WorkflowyException
     * @return bool|WorkflowySublist
     */
    public function searchSublist($expression, $get_all = false)
    {
        if (!is_string($expression) || preg_match($expression, null) === false)
        {
            throw new WorkflowyException('Search expression must be a valid regular expression');
        }
        $get_all = $get_all ? true : false;
        $matches = array();
        if (preg_match($expression, $this->name))
        {
            if (!$get_all)
            {
                return $this;
            }
            $matches[] = $this;
        }
        foreach ($this->sublists as $child)
        {
            $match = $child->searchSublist($expression, $get_all);
            if ($match !== false && !$get_all)
            {
                return $match;
            }
            if ($get_all)
            {
                $matches = array_merge($matches, $match);
            }
        }
        return $get_all ? $matches : false;
    }

    /**
     * Sets the list name
     * @param string $name
     * @throws WorkflowyException
     */
    public function setName($name)
    {
        if (!is_string($name))
        {
            throw new WorkflowyException('Name must be a string');
        }
        $this->transport->listRequest('edit', array('projectid' => $this->id, 'name' => $name));
    }

    /**
     * Sets the list description
     * @param string $description
     * @throws WorkflowyException
     */
    public function setDescription($description)
    {
        if (!is_string($description))
        {
            throw new WorkflowyException('Description must be a string');
        }
        $this->transport->listRequest('edit', array('projectid' => $this->id, 'description' => $description));
    }

    /**
     * Sets the list completion status
     * @param bool $complete
     * @throws WorkflowyException
     */
    public function setComplete($complete)
    {
        if (!is_bool($complete))
        {
            throw new WorkflowyException('Completion status must be boolean');
        }
        $this->transport->listRequest(($complete ? 'complete' : 'uncomplete'), array('projectid' => $this->id));
    }

    /**
     * Sets the parent and priority of the list
     * @param WorkflowySublist $parent_sublist
     * @param int $priority
     * @throws WorkflowyException
     */
    public function setParent($parent_sublist, $priority)
    {
        if (empty($parent_sublist) || !is_a($parent_sublist, __CLASS__))
        {
            throw new WorkflowyException('Parent sublist must be a ' . __CLASS__ . ' instance');
        }
        $this->transport->listRequest('move', array(
            'projectid' => $this->id,
            'parentid'  => $parent_sublist->getID(),
            'priority'  => intval($priority)
        ));
    }

    /**
     * Delets the current sublist
     * This will also delete its children
     */
    public function delete()
    {
        $this->transport->listRequest('delete', array('projectid' => $this->id));
    }

    /**
     * Creates a sublist
     * @param string $name
     * @param string $description
     * @param int $priority
     * @throws WorkflowyException
     */
    public function createSublist($name, $description, $priority)
    {
        if ((!empty($name) && !is_string($name)) || (!empty($description) && !is_string($description)))
        {
            throw new WorkflowyException('Name and description must be strings');
        }
        $new_id = $this->generateID();
        $this->transport->listRequest('create', array(
            'projectid' => $new_id,
            'parentid'  => $this->id,
            'priority'  => intval($priority)
        ));
        if (!empty($name))
        {
            $this->transport->listRequest('edit', array('projectid' => $new_id, 'name' => $name));
        }
        if (!empty($description))
        {
            $this->transport->listRequest('edit', array('projectid' => $new_id, 'description' => $description));
        }
    }

    private function generateID()
    {
        //return((1+Math.random())*65536|0).toString(16).substring(1)
        // k()+k()+" - "+k()+" - "+k()+" - "+k()+" - "+k()+k()+k()
        return preg_replace_callback('#r#', function ()
        {
            return substr(base_convert((1 + ((float)rand() / (float)getrandmax())) * 65536 | 0, 10, 16), 1);
        }, 'rr-r-r-r-rrr');
    }

}
