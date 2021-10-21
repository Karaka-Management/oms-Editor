<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package    tests
 * @copyright  2013 Dennis Eichhorn
 * @license    OMS License 1.0
 * @version    1.0.0
 * @link       https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Editor\tests\Models;

use Modules\Admin\Models\NullAccount;
use Modules\Editor\Models\EditorDoc;
use Modules\Editor\Models\EditorDocMapper;
use phpOMS\Utils\RnG\Text;

/**
 * @internal
 */
final class EditorDocMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Editor\Models\EditorDocMapper
     * @group module
     */
    public function testCRUD() : void
    {
        $doc = new EditorDoc();

        $doc->createdBy = new NullAccount(1);
        $doc->title     = 'Title';
        $doc->content   = 'Content';
        $doc->setVirtualPath('/some/test/path');

        $id = EditorDocMapper::create($doc);
        self::assertGreaterThan(0, $doc->getId());
        self::assertEquals($id, $doc->getId());

        $docR = EditorDocMapper::get($doc->getId());
        self::assertEquals($doc->createdAt->format('Y-m-d'), $docR->createdAt->format('Y-m-d'));
        self::assertEquals($doc->createdBy->getId(), $docR->createdBy->getId());
        self::assertEquals($doc->content, $docR->content);
        self::assertEquals($doc->title, $docR->title);
        self::assertEquals($doc->getVirtualPath(), $docR->getVirtualPath());
    }

    /**
     * @group volume
     * @group module
     * @coversNothing
     */
    public function testVolume() : void
    {
        for ($i = 0; $i < 100; ++$i) {
            $text = new Text();
            $doc  = new EditorDoc();

            // Test other

            $doc->createdBy = new NullAccount(\mt_rand(1, 1));
            $doc->title     = $text->generateText(\mt_rand(3, 7));
            $doc->content   = $text->generateText(\mt_rand(20, 500));
            $doc->setVirtualPath('/some/test/path');

            $id = EditorDocMapper::create($doc);
        }
    }
}
