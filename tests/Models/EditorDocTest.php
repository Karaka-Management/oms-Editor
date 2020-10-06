<?php
/**
 * Orange Management
 *
 * PHP Version 7.4
 *
 * @package    tests
 * @copyright  2013 Dennis Eichhorn
 * @license    OMS License 1.0
 * @version    1.0.0
 * @link       https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Tasks\tests\Models;

use Modules\Admin\Models\NullAccount;
use Modules\Editor\Models\EditorDoc;
use Modules\Tag\Models\Tag;

/**
 * @internal
 */
class EditorDocTest extends \PHPUnit\Framework\TestCase
{
    private EditorDoc $doc;

    public function setUp() : void
    {
        $this->doc = new EditorDoc();
    }

    public function testDefault() : void
    {
        self::assertEquals(0, $this->doc->getId());
        self::assertEquals(0, $this->doc->getCreatedBy()->getId());
        self::assertEquals('', $this->doc->getTitle());
        self::assertEquals('', $this->doc->getContent());
        self::assertEquals('', $this->doc->getPlain());
        self::assertEquals([], $this->doc->getTags());
        self::assertEquals((new \DateTime('now'))->format('Y-m-d'), $this->doc->getCreatedAt()->format('Y-m-d'));
    }

    public function testCreatedByInputOutput() : void
    {
        $this->doc->setCreatedBy(new NullAccount(1));
        self::assertEquals(1, $this->doc->getCreatedBy()->getId());
    }

    public function testTitleInputOutput() : void
    {
        $this->doc->setTitle('Title');
        self::assertEquals('Title', $this->doc->getTitle());
    }

    public function testContentInputOutput() : void
    {
        $this->doc->setContent('Content');
        self::assertEquals('Content', $this->doc->getContent());
    }

    public function testPlainInputOutput() : void
    {
        $this->doc->setPlain('Plain');
        self::assertEquals('Plain', $this->doc->getPlain());
    }

    public function testPathInputOutput() : void
    {
        $this->doc->setPath('/some/test/path');
        self::assertEquals('/some/test/path', $this->doc->getPath());
    }

    public function testTagInputOutput() : void
    {
        $tag = new Tag();
        $tag->setTitle('Tag');

        $this->doc->addTag($tag);
        self::assertCount(1, $this->doc->getTags());
    }

    public function testSerialization() : void
    {
        $this->doc->setCreatedBy(new NullAccount(1));
        $this->doc->setTitle('Title');
        $this->doc->setContent('Content');
        $this->doc->setPlain('Plain');
        $this->doc->setPath('/some/path');
        $arr = [
            'id'        => 0,
            'title'     => $this->doc->getTitle(),
            'plain'     => $this->doc->getPlain(),
            'content'   => $this->doc->getContent(),
            'createdAt' => $this->doc->getCreatedAt(),
            'createdBy' => $this->doc->getCreatedBy(),
        ];
        self::assertEquals($arr, $this->doc->toArray());
        self::assertEquals($arr, $this->doc->jsonSerialize());
        self::assertEquals(\json_encode($arr), $this->doc->__toString());
    }
}
