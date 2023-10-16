<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Editor\tests\Models;

use Modules\Editor\Models\NullEditorDocHistory;

/**
 * @internal
 */
final class NullEditorDocHistoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Editor\Models\NullEditorDocHistory
     * @group framework
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Editor\Models\EditorDocHistory', new NullEditorDocHistory());
    }

    /**
     * @covers Modules\Editor\Models\NullEditorDocHistory
     * @group framework
     */
    public function testId() : void
    {
        $null = new NullEditorDocHistory(2);
        self::assertEquals(2, $null->getId());
    }

    /**
     * @covers Modules\Editor\Models\NullEditorDocHistory
     * @group framework
     */
    public function testJsonSerialize() : void
    {
        $null = new NullEditorDocHistory(2);
        self::assertEquals(['id' => 2], $null);
    }
}