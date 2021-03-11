<?php

declare(strict_types=1);

namespace JohanSatge\WorkFlowy\Model;

class Document
{
    /**
     * Root item.
     *
     * @var Item
     */
    private Item $root;

    /**
     * All items.
     *
     * Key is the id of the list, value the actual list object. Useful for quick lookups where the id is known.
     * This may seem wasteful to have everything twice, but since the item is an object it's passed by reference only.
     *
     * @var array<string,Item>
     */
    private array $items;

    private string $ownerEmail;

    /**
     * @param Item   $root
     * @param Item[] $items
     * @param string $ownerEmail
     */
    public function __construct(
        Item $root,
        array $items,
        string $ownerEmail
    ) {
        $this->root = $root;
        $this->items = $items;
        $this->ownerEmail = $ownerEmail;
    }

    /**
     * Get the root item.
     */
    public function getRoot(): Item
    {
        return $this->root;
    }

    /**
     * Gets all items in one flat list.
     *
     * @return array<string,Item>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Email of the owner of this list.
     */
    public function getOwnerEmail(): string
    {
        return $this->ownerEmail;
    }

    /**
     * Get single item with known Id.
     *
     * This will fetch an item if it's present in the data. This makes access to known items very fast.
     *
     * @param string $id Id of item to return
     *
     * @return Item|null returns null if item doesn't exist.
     */
    public function getItem(string $id): ?Item
    {
        return $this->items[$id] ?? null;
    }
}
