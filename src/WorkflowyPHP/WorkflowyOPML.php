<?php

/* WorkFlowyPHP - https://github.com/johansatge/workflowy-php */

namespace WorkFlowyPHP;

class WorkFlowyOPML
{

    private $sublist;

    /**
     * Builds an OPML exporter by using the given list
     * @param WorkFlowySublist $sublist
     * @throws WorkFlowyException
     */
    public function __construct($sublist)
    {
        if (!is_a($sublist, '\WorkFlowyPHP\WorkFlowySublist'))
        {
            throw new WorkFlowyException('Sublist must be a WorkFlowySublist instance');
        }
        $this->sublist = $sublist;
    }

    /**
     * Exports the given list
     * @param int $depth
     * @return string
     */
    public function export($depth = 0)
    {
        $depth = intval($depth);
        return $this->getHeader($depth) . $this->getBody($depth) . $this->getFooter($depth);
    }

    /**
     * Gets the OPML header
     * @param int $depth
     * @return string
     */
    private function getHeader($depth)
    {
        $opml = '';
        if ($depth == 0)
        {
            $opml .= '<?xml version="1.0"?>' . "\n" . '<opml version="2.0">' . "\n" . '<head>' . "\n" . '<ownerEmail></ownerEmail>' . "\n" . '</head>' . "\n" . '<body>' . "\n";
        }
        if ($this->sublist->getParent() !== false)
        {
            $tag = '<outline%s text="%s" _note="%s"' . (count($this->sublist->getSublists()) == 0 ? ' />' : '>') . "\n";
            $opml .= sprintf($tag, $this->sublist->isComplete() ? ' _complete="true"' : '', $this->esc($this->sublist->getName()), $this->esc($this->sublist->getDescription()));
        }
        return $opml;
    }

    /**
     * Gets the OPML body
     * @param int $depth
     * @return string
     */
    private function getBody($depth)
    {
        $opml = '';
        foreach ($this->sublist->getSublists() as $sublist)
        {
            $exporter = new WorkFlowyOPML($sublist);
            $opml .= $exporter->export($depth + 1);
        }
        return $opml;
    }

    /**
     * Gets the OPML footer
     * @param int $depth
     * @return string
     */
    private function getFooter($depth)
    {
        $opml = '';
        if ($this->sublist->getParent() !== false)
        {
            $opml .= count($this->sublist->getSublists()) > 0 ? '</outline>' . "\n" : '';
        }
        if ($depth == 0)
        {
            $opml .= '</body>' . "\n" . '</opml>';
        }
        return $opml;
    }

    /**
     * Escapes the given string
     * @param string $string
     * @return string
     */
    private function esc($string)
    {
        return str_replace('"', '\"', stripslashes($string));
    }

}
