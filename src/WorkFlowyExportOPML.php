<?php

declare(strict_types=1);

namespace JohanSatge\WorkFlowy;

use JohanSatge\WorkFlowy\Model\Document;
use JohanSatge\WorkFlowy\Model\Item;

final class WorkFlowyExportOPML
{
    public static function exportDocument(Document $document): string
    {
        $opml = '<?xml version="1.0"?><opml version="2.0"><head><ownerEmail>'
            .$document->getOwnerEmail()
            .'</ownerEmail></head><body>';

        // The first item root is a faked item, so this is how we skip that
        $root = $document->getRoot();
        foreach ($root->getChildren() as $child) {
            $opml .= static::recurisveItemExport($child);
        }

        $opml .= '</body></opml>';

        return $opml;
    }

    private static function recurisveItemExport(Item $item): string
    {
        $opml = '<outline';

        if (!empty($item->getName())) {
            $opml .= ' text="'.htmlspecialchars($item->getName()).'"';
        }

        if (!empty($item->getDescription())) {
            $opml .= ' _note="'.htmlspecialchars($item->getDescription()).'"';
        }

        if ($item->isComplete()) {
            $opml .= ' _complete="true"';
        }

        if (\count($item->getChildren())) {
            $opml .= '>';
            foreach ($item->getChildren() as $child) {
                $opml .= static::recurisveItemExport($child);
            }
            $opml .= '</outline>';
        } else {
            $opml .= ' \>';
        }

        return $opml;
    }
}
