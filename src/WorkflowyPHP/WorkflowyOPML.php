<?php

/*
 * This file is part of the WorkflowyPHP package.
 *
 * (c) Johan Satgé
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace WorkflowyPHP;

class WorkflowyOPML
{

    private $sublist;

    /**
     * Builds an OPML exporter by using the given list
     * @param WorkflowySublist $sublist
     * @throws WorkflowyException
     */
    public function __construct($sublist)
    {
        if (!is_a($sublist, '\WorkflowyPHP\WorkflowySublist'))
        {
            throw new WorkflowyException('Sublist must be a WorkflowySublist instance');
        }
        $this->sublist = $sublist;
    }

    /**
     * Exports the given list
     * @return string
     */
    public function export()
    {
        // @todo
        /*
        <?xml version="1.0"?>
        <opml version="2.0">
            <head>
                <ownerEmail>workflowy1@yopmail.com</ownerEmail>
            </head>
            <body>
                <outline text="test avec sub" _note="Description: 17-12-2014 11:33:12">
                    <outline text="test2 sub1" >
                        <outline text="test2 sub1 sub1" _note="23-12-2014 17:00:50" />
                    </outline>
                    <outline _complete="true" text="test2 sub2 complete" /></outline>
            </body>
        </opml>
        */
    }

}
