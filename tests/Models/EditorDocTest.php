<?php
/**
 * Jingga
 *
 * PHP Version 8.1
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
use Modules\Media\Models\Media;
use Modules\Tag\Models\Tag;

/**
 * @internal
 */
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

    /**
     * @covers Modules\Editor\Models\EditorDoc
     * @group module
     */
    public function testDefault() : void
    {
        self::assertEquals(0, $this->doc->id);
        self::assertEquals(0, $this->doc->createdBy->id);
        self::assertEquals('', $this->doc->title);
        self::assertEquals('', $this->doc->content);
        self::assertEquals('', $this->doc->plain);
        self::assertEquals([], $this->doc->getTags());
        self::assertEquals([], $this->doc->getMedia());
        self::assertEquals((new \DateTime('now'))->format('Y-m-d'), $this->doc->createdAt->format('Y-m-d'));
    }

    /**
     * @covers Modules\Editor\Models\EditorDoc
     * @group module
     */
    public function testCreatedByInputOutput() : void
    {
        $this->doc->createdBy = new NullAccount(1);
        self::assertEquals(1, $this->doc->createdBy->id);
    }

    /**
     * @covers Modules\Editor\Models\EditorDoc
     * @group module
     */
    public function testTitleInputOutput() : void
    {
        $this->doc->title = 'Title';
        self::assertEquals('Title', $this->doc->title);
    }

    /**
     * @covers Modules\Editor\Models\EditorDoc
     * @group module
     */
    public function testContentInputOutput() : void
    {
        $this->doc->content = 'Content';
        self::assertEquals('Content', $this->doc->content);
    }

    /**
     * @covers Modules\Editor\Models\EditorDoc
     * @group module
     */
    public function testPlainInputOutput() : void
    {
        $this->doc->plain = 'Plain';
        self::assertEquals('Plain', $this->doc->plain);
    }

    /**
     * @covers Modules\Editor\Models\EditorDoc
     * @group module
     */
    public function testPathInputOutput() : void
    {
        $this->doc->setVirtualPath('/some/test/path');
        self::assertEquals('/some/test/path', $this->doc->getVirtualPath());
    }

    /**
     * @covers Modules\Editor\Models\EditorDoc
     * @group module
     */
    public function testTagInputOutput() : void
    {
        $tag = new Tag();
        $tag->setL11n('Tag');

        $this->doc->addTag($tag);
        self::assertCount(1, $this->doc->getTags());
    }

    /**
     * @covers Modules\Editor\Models\EditorDoc
     * @group module
     */
    public function testMediaInputOutput() : void
    {
        $this->doc->addMedia(new Media());
        self::assertCount(1, $this->doc->getMedia());
    }

    /**
     * @covers Modules\Editor\Models\EditorDoc
     * @group module
     */
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
