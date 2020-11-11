<?php

declare(strict_types=1);

namespace JohanSatge\WorkFlowy;

use JohanSatge\WorkFlowy\Model\Document;
use JohanSatge\WorkFlowy\Model\Item;

final class WorkFlowyParser
{
    /**
     * @var array<string, Item>
     */
    private static array $items;
    private static int $dateJoinedTimestampInSeconds;

    /**
     * @param array<mixed> $data
     *
     * @return Document
     * @throws WorkFlowyException
     */
    public static function parseDocument(array $data): Document
    {
        $rawDocument = $data['projectTreeData']['mainProjectTreeInfo']['rootProjectChildren'] ?? [];
        static::$dateJoinedTimestampInSeconds = $data['projectTreeData']['mainProjectTreeInfo']['dateJoinedTimestampInSeconds'] ?? 0;
        static::$items = [];
        $root = static::parseItem(
            ['id' => WorkFlowyClient::ROOT_ITEM_ID, 'nm' => null, 'no' => null, 'cp' => null, 'lm' => 0, 'ch' => $rawDocument]
        );
        $ownerEmail = $data['user']['email'] ?? '';
        $document = new Document($root, static::$items, $ownerEmail);
        static::$items = [];

        return $document;
    }

    /**
     * Recursive parser for list data.
     *
     * @param array<mixed> $data
     * @param string       $parentId
     */
    private static function parseItem(array $data, string $parentId = ''): Item
    {
        $id = $data['id'] ?? '';
        $name = $data['nm'] ?? '';
        $description = $data['no'] ?? '';
        // Complete & last modified dates are offsets, starting at the user's registration date (in seconds)
        $complete = !empty($data['cp']) ? (static::$dateJoinedTimestampInSeconds + (int)$data['cp']) : null;
        $lastModified = !empty($data['lm']) ? (static::$dateJoinedTimestampInSeconds + (int)$data['lm']) : 0;

        // Board feature just sets an item to be a board with this metadata field
        // Next level below that is the columns, and the level below that are items in the columns
        $isBoard = false;
        if (isset($data['metadata']['layoutMode']) && 'board' === $data['metadata']['layoutMode']) {
            $isBoard = true;
        }

        $children = [];
        $rawChildren = $data['ch'] ?? [];
        foreach ($rawChildren as $rawChild) {
            $children[] = static::parseItem($rawChild, $id);
        }
        $item = new Item($id, $name, $description, $complete, $lastModified, $children, $parentId, $isBoard);

        static::$items[$id] = $item;

        return $item;
    }
}
