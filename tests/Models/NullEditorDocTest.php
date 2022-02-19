<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Editor\tests\Models;

use Modules\Editor\Models\NullEditorDoc;

/**
 * @internal
 */
final class NullEditorDocTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Editor\Models\NullEditorDoc
     * @group module
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Editor\Models\EditorDoc', new NullEditorDoc());
    }

    /**
     * @covers Modules\Editor\Models\NullEditorDoc
     * @group module
     */
    public function testId() : void
    {
        $null = new NullEditorDoc(2);
        self::assertEquals(2, $null->getId());
    }
}
