<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package    tests
 * @copyright  2013 Dennis Eichhorn
 * @license    OMS License 2.0
 * @version    1.0.0
 * @link       https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Tasks\tests\Models;

use Modules\Admin\Models\NullAccount;
use Modules\Editor\Models\EditorDoc;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Modules\Editor\Models\EditorDoc::class)]
final class EditorDocTest extends \PHPUnit\Framework\TestCase
{
    private EditorDoc $doc;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->doc = new EditorDoc();
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testDefault() : void
    {
        self::assertEquals(0, $this->doc->id);
        self::assertEquals(0, $this->doc->createdBy->id);
        self::assertEquals('', $this->doc->title);
        self::assertEquals('', $this->doc->content);
        self::assertEquals('', $this->doc->plain);
        self::assertEquals([], $this->doc->tags);
        self::assertEquals([], $this->doc->files);
        self::assertEquals((new \DateTime('now'))->format('Y-m-d'), $this->doc->createdAt->format('Y-m-d'));
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testCreatedByInputOutput() : void
    {
        $this->doc->createdBy = new NullAccount(1);
        self::assertEquals(1, $this->doc->createdBy->id);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testTitleInputOutput() : void
    {
        $this->doc->title = 'Title';
        self::assertEquals('Title', $this->doc->title);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testContentInputOutput() : void
    {
        $this->doc->content = 'Content';
        self::assertEquals('Content', $this->doc->content);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testPlainInputOutput() : void
    {
        $this->doc->plain = 'Plain';
        self::assertEquals('Plain', $this->doc->plain);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testPathInputOutput() : void
    {
        $this->doc->setVirtualPath('/some/test/path');
        self::assertEquals('/some/test/path', $this->doc->getVirtualPath());
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testSerialization() : void
    {
        $this->doc->createdBy = new NullAccount(1);
        $this->doc->title     = 'Title';
        $this->doc->content   = 'Content';
        $this->doc->plain     = 'Plain';
        $this->doc->setVirtualPath('/some/path');
        $arr = [
            'id'        => 0,
            'title'     => $this->doc->title,
            'plain'     => $this->doc->plain,
            'content'   => $this->doc->content,
            'createdAt' => $this->doc->createdAt,
            'createdBy' => $this->doc->createdBy,
        ];
        self::assertEquals($arr, $this->doc->toArray());
        self::assertEquals($arr, $this->doc->jsonSerialize());
        self::assertEquals(\json_encode($arr), $this->doc->__toString());
    }
}
