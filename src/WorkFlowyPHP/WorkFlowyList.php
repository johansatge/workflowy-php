<?php

/* WorkFlowyPHP - https://github.com/johansatge/workflowy-php */

namespace WorkFlowyPHP;

class WorkFlowyList
{

    private $transport;
    private $parents;
    private $sublists;
    private $dateJoinedTimestampInSeconds = 0;

    /**
     * Builds a WorkFlowy list
     * The class holds the hierarchic relations between all its sublists (parents / children)
     * @param string $session_id
     * @throws WorkFlowyException
     */
    public function __construct($session_id)
    {
        $this->transport = new WorkFlowyTransport($session_id);
    }

    /**
     * Returns the main list
     * @return WorkFlowySublist
     */
    public function getList()
    {
        $data      = $this->transport->apiRequest('get_initialization_data');
        $raw_lists = array();
        if (!empty($data['projectTreeData']['mainProjectTreeInfo']['rootProjectChildren']))
        {
            $raw_lists = $data['projectTreeData']['mainProjectTreeInfo']['rootProjectChildren'];
        }
        if (!empty($data['projectTreeData']['mainProjectTreeInfo']['dateJoinedTimestampInSeconds']))
        {
            $this->dateJoinedTimestampInSeconds = $data['projectTreeData']['mainProjectTreeInfo']['dateJoinedTimestampInSeconds'];
        }
        $this->parents  = array();
        $this->sublists = array();
        return $this->parseList(array('id' => 'None', 'nm' => null, 'no' => null, 'cp' => null, 'lm' => 0, 'ch' => $raw_lists), false);
    }

    /**
     * Recursively parses the given list and builds an object
     * @param array $raw_list
     * @param string $parent_id
     * @return WorkFlowyList
     */
    private function parseList($raw_list, $parent_id)
    {
        $id            = !empty($raw_list['id']) ? $raw_list['id'] : '';
        $name          = !empty($raw_list['nm']) ? $raw_list['nm'] : '';
        $description   = !empty($raw_list['no']) ? $raw_list['no'] : '';
        $raw_sublists  = !empty($raw_list['ch']) && is_array($raw_list['ch']) ? $raw_list['ch'] : array();
        // Complete & last modified dates are offsets, starting at the user's registration date (in minutes)
        $complete      = !empty($raw_list['cp']) ? $this->dateJoinedTimestampInSeconds + ($raw_list['cp'] * 60) : false;
        $last_modified = !empty($raw_list['lm']) ? $this->dateJoinedTimestampInSeconds + ($raw_list['lm'] * 60) : 0;
        $sublists      = array();
        foreach ($raw_sublists as $raw_sublist)
        {
            $sublists[] = $this->parseList($raw_sublist, $id);
        }
        $sublist = new WorkFlowySublist($id, $name, $description, $complete, $last_modified, $sublists, $this, $this->transport);
        if (!empty($parent_id))
        {
            $this->parents[$id] = $parent_id;
        }
        $this->sublists[$id] = $sublist;
        return $sublist;
    }

    /**
     * Tries to return the parent of the given sublist ID
     * @param string $id
     * @return bool
     */
    public function getSublistParent($id)
    {
        $parent_id = is_string($id) && !empty($this->parents[$id]) ? $this->parents[$id] : false;
        return !empty($this->sublists[$parent_id]) ? $this->sublists[$parent_id] : false;
    }

}
