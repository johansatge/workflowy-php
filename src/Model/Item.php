<?php

declare(strict_types=1);

namespace JohanSatge\WorkFlowy\Model;

/**
 * Individual items of
 */
class Item
{
    public const FIELD_NAME = 'name';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_COMPLETE = 'complete';
    public const FIELD_BOARD = 'board';

    public const ALL_FIELDS = [
        self::FIELD_NAME,
        self::FIELD_DESCRIPTION,
        self::FIELD_COMPLETE,
        self::FIELD_BOARD,
    ];

    /**
     * @var string Id of this item.
     */
    private string $id;

    /**
     * @var string Id of the parent item.
     */
    private string $parentId;

    /**
     * Name of item
     * .
     * This is the main text you see on an item.
     *
     * @var string
     */
    private string $name;

    /**
     * Description text.
     *
     * The extra long text that you can add to an item and is shown below it.
     *
     * @var string
     */
    private string $description;

    /**
     * Completion of item.
     *
     * Null means it's not complete yet.
     * Otherwise it's a timestamp (relative to your registration date) when it was marked as complete.
     * The value of 1 is used as a placeholder to set it when making an item completed.
     *
     * @var int|null
     */
    private ?int $complete;

    /**
     * Timestamp when this item was last modified.
     * Is relative to users registration timestamp.
     *
     * @var int
     */
    private int $lastModified;

    /**
     * Children within this item.
     *
     * @var Item[]
     */
    private array $children = [];

    /**
     * Flags an item as board or not.
     *
     * @var bool
     */
    private bool $isBoard;

    /**
     * Changed fields are stored here and the update function can then use this to update the fields.
     *
     * Saves on API calls where possible.
     *
     * @var array<string,bool> Changes by fields
     */
    private array $changeStack = [];

    /**
     * @param string      $id
     * @param string      $name
     * @param string      $description
     * @param int|null    $complete
     * @param int         $lastModified
     * @param array<Item> $children
     * @param string      $parentId
     * @param bool        $isBoard
     */
    public function __construct(
        string $id = '',
        string $name = '',
        string $description = '',
        ?int $complete = null,
        int $lastModified = 0,
        array $children = [],
        string $parentId = '',
        bool $isBoard = false
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->complete = $complete;
        $this->lastModified = $lastModified;
        $this->parentId = $parentId;
        $this->isBoard = $isBoard;

        foreach ($children as $child) {
            if (\is_a($child, __CLASS__)) {
                $this->children[] = $child;
            }
        }
    }

    /**
     * Id of this specific item.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Id of this items parent.
     */
    public function getParentId(): string
    {
        return $this->parentId;
    }

    /**
     * Set the name of the item.
     *
     * @param string $name
     */
    public function setName(string $name = ''): void
    {
        $this->name = $name;

        $this->changeStack[self::FIELD_NAME] = true;
    }

    /**
     * Name of the item.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the item description.
     *
     * @param string $description
     */
    public function setDescription($description = ''): void
    {
        $this->description = $description;

        $this->changeStack[self::FIELD_DESCRIPTION] = true;
    }

    /**
     * Description of this item.
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Returns the last modification time.
     *
     * @return int
     */
    public function getLastModifiedTime(): int
    {
        return $this->lastModified;
    }

    /**
     * Sets the item completion status.
     *
     * @param bool $complete
     */
    public function setComplete(bool $complete): void
    {
        if (true === $complete && null === $this->complete) {
            $this->complete = 1;
            $this->changeStack[self::FIELD_COMPLETE] = true;
        } elseif (false === $complete && 1 <= $this->complete) {
            $this->complete = null;
            $this->changeStack[self::FIELD_COMPLETE] = true;
        }
    }

    /**
     * Returns the completed time (if available, false otherwise)
     *
     * @return int
     */
    public function getCompletedTime(): ?int
    {
        return $this->complete;
    }

    /**
     * Is this list marked as complete.
     */
    public function isComplete(): bool
    {
        return null !== $this->complete;
    }

    /**
     * Returns an array of children.
     *
     * @return Item[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Empty the change stack after saving.
     */
    public function clearChangeStack(): void
    {
        $this->changeStack = [];
    }

    /**
     * Get the change stack with all changed fields.
     *
     * @return array<string,bool>
     */
    public function getChangeStack(): array
    {
        return $this->changeStack;
    }

    /**
     * Set an item to be a board.
     *
     * @param bool $isBoard
     */
    public function setBoard(bool $isBoard): void
    {
        // Only really need to do anything on change
        if ($isBoard !== $this->isBoard) {
            $this->changeStack[self::FIELD_BOARD] = true;
            $this->isBoard = $isBoard;
        }
    }

    /**
     * Is this item a board.
     *
     * @return bool
     */
    public function isBoard(): bool
    {
        return $this->isBoard;
    }

    /**
     * Search recursively for items within this item by name.
     *
     * @param string $expression Regular expression
     *
     * @return Item[]
     */
    public function searchItem(string $expression): array
    {
        $matches = [];

        if (\preg_match($expression, $this->name)) {
            $matches[] = $this;
        }

        foreach ($this->children as $child) {
            $match = $child->searchItem($expression);
            $matches = \array_merge($matches, $match);
        }

        return $matches;
    }

    /**
     * Helper to get a link to this item.
     */
    public function getLink(): string
    {
        // Root has the simplest link
        if (36 !== \strlen($this->id)) {
            return 'https://workflowy.com/';
        }

        return 'https://workflowy.com/#/'.\substr($this->id, (int) \strrpos($this->id, '-'));
    }
}
