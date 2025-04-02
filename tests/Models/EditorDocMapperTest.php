<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package    tests
 * @copyright  2013 Dennis Eichhorn
 * @license    OMS License 2.2
 * @version    1.0.0
 * @link       https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Editor\tests\Models;

use Modules\Admin\Models\NullAccount;
use Modules\Editor\Models\EditorDoc;
use Modules\Editor\Models\EditorDocMapper;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Modules\Editor\Models\EditorDocMapper::class)]
final class EditorDocMapperTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testCRUD() : void
    {
        $doc = new EditorDoc();

        $doc->createdBy = new NullAccount(1);
        $doc->title     = 'Title';
        $doc->content   = 'Content';
        $doc->setVirtualPath('/some/test/path');

        $id = EditorDocMapper::create()->execute($doc);
        self::assertGreaterThan(0, $doc->id);
        self::assertEquals($id, $doc->id);

        $docR = EditorDocMapper::get()->where('id', $doc->id)->execute();
        self::assertEquals($doc->createdAt->format('Y-m-d'), $docR->createdAt->format('Y-m-d'));
        self::assertEquals($doc->createdBy->id, $docR->createdBy->id);
        self::assertEquals($doc->content, $docR->content);
        self::assertEquals($doc->title, $docR->title);
        self::assertEquals($doc->getVirtualPath(), $docR->getVirtualPath());

        $docR2 = EditorDocMapper::getByVirtualPath('/some/test/path', 1)->execute();
        self::assertEquals($docR->id, \reset($docR2)->id);
    }
}
