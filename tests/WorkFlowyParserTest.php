<?php

declare(strict_types=1);

namespace JohanSatge\WorkFlowy\Tests;

use JohanSatge\WorkFlowy\Model\Item;
use JohanSatge\WorkFlowy\WorkFlowyParser;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \JohanSatge\WorkFlowy\WorkFlowyParser
 */
class WorkFlowyParserTest extends TestCase
{
    /**
     * @covers ::parseDocument
     */
    public function testParseDocument(): void
    {
        $json = \file_get_contents(__DIR__.'/fixtures/get_initialization_data.json');
        $data = \json_decode((string) $json, true, 512, JSON_THROW_ON_ERROR);
        $document = WorkFlowyParser::parseDocument($data);

        $root = $document->getRoot();

        /** @var Item[] $rootChildren */
        $rootChildren = $root->getChildren();
        self::assertCount(11, $rootChildren);

        // Simple case
        self::assertEquals('2c2d06f0-176b-e9c9-41ed-343370ff4bae', $rootChildren[0]->getId());
        self::assertEquals('In Root Normal', $rootChildren[0]->getName());
        self::assertEquals('', $rootChildren[0]->getDescription());
        self::assertFalse($rootChildren[0]->isComplete());
        self::assertFalse($rootChildren[0]->isBoard());

        // Complete
        self::assertEquals('1c56ffe5-46df-36d8-ee2b-9c701906fd13', $rootChildren[3]->getId());
        self::assertEquals('In Root Completed', $rootChildren[3]->getName());
        self::assertTrue($rootChildren[3]->isComplete());

        // Contains a note
        self::assertEquals('4dcd8072-4800-6e56-107b-fddee7c530a2', $rootChildren[1]->getId());
        self::assertEquals('In Root Normal with Note', $rootChildren[1]->getName());
        self::assertEquals('Note on Item', $rootChildren[1]->getDescription());

        // Sub items
        self::assertEquals('9a2ebabe-973d-d09b-6cc2-feeb29923439', $rootChildren[8]->getId());
        self::assertEquals('Sublists', $rootChildren[8]->getName());
        self::assertCount(3, $rootChildren[8]->getChildren());
        self::assertEquals('496db1c8-74c9-83f2-1a03-820e14257538', $rootChildren[8]->getChildren()[2]->getId());

        // Boards
        self::assertEquals('ce9b588a-008c-83a7-060f-3a59b5409740', $rootChildren[9]->getId());
        self::assertEquals('Boards', $rootChildren[9]->getName());
        self::assertEquals('bb925f2c-8ec7-34f6-add7-8c7004f4b3d8', $rootChildren[9]->getChildren()[0]->getId());
        self::assertTrue($rootChildren[9]->getChildren()[0]->isBoard());

        // Mirrors
        self::assertEquals('bb5b0c33-a000-2bf2-3dc7-a228757ca7c5', $rootChildren[10]->getId());
        self::assertEquals('Mirrors', $rootChildren[10]->getName());
        // @TODO: Add test cases for mirrors
    }
}
