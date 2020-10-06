<?php
/**
 * Orange Management
 *
 * PHP Version 7.4
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Editor\tests\Models;

use Modules\Editor\Models\NullEditorDoc;

/**
 * @internal
 */
final class NullEditorDocTest extends \PHPUnit\Framework\TestCase
{
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Editor\Models\EditorDoc', new NullEditorDoc());
    }

    public function testId() : void
    {
        $null = new NullEditorDoc(2);
        self::assertEquals(2, $null->getId());
    }
}
