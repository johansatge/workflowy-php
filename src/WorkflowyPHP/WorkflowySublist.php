<?php

/*
 * This file is part of the WorkflowyPHP package.
 *
 * (c) Johan Satgé
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

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
     * /!\ Keep in mind than on update, the list content will not be impacted (for instance, when moving a sublist to an other parent, or when modyfing the description)
     * @param string $id
     * @param string $name
     * @param string $description
     * @param bool $complete
     * @param array $sublists
     * @param WorkflowyList $list
     * @param WorkflowyTransport $transport
     */
    public function __construct($id, $name, $description, $complete, $sublists, $list, $transport)
    {
        $this->id          = is_string($id) ? $id : '';
        $this->name        = is_string($name) ? $name : '';
        $this->description = is_string($description) ? $description : '';
        $this->complete    = $complete ? true : false;
        $this->sublists    = $sublists; // @todo check type
        $this->list        = $list; // @todo check type
        $this->transport   = $transport; // @todo check type
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

    public function isAncestorOf()
    {
        // @todo
    }

    public function isChildOf()
    {
        // @todo
    }

    /**
     * Returns the OPML view
     * @return string
     */
    public function getOPML()
    {
        $opml = new WorkflowyOPML($this);
        return $opml->export();
    }

    /**
     * Search recursively if the list has the requested name
     * @todo allow regexps ?
     * @todo return multiple results ?
     * @param string $name
     * @return bool|WorkflowyList
     */
    public function search($name)
    {
        // @todo check string
        if (preg_match('/' . preg_quote($name, '/') . '/i', $this->name))
        {
            return $this;
        }
        foreach ($this->sublists as $child)
        {
            $match = $child->search($name);
            if ($match !== false)
            {
                return $match;
            }
        }
        return false;
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

    public function setParent($parent, $priority)
    {
        // @todo
        /*
        if (empty($parent_list) || get_class($parent_list) !== __CLASS__)
        {
            throw new WorkflowyError('The method requires a ' . __CLASS__ . ' object');
        }
        $this->session->performListRequest('move', array(
            'projectid' => $this->id,
            'parentid'  => $parent_list->id(),
            'priority'  => intval($priority)
        ), $this->clientID);

         */
    }

    public function delete()
    {
        // @todo
    }

    public function createSublist()
    {
        // @todo
        /*
             (object)array(
                'type' => 'create',
                'data' => (object)array(
                    'projectid' => $generated_test_id,
                    'parentid'  => 'None',
                    'priority'  => 6
                )
            )
            /!\ after created, launch edit()
        }*/
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
