<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Editor\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Editor\Models;

use Modules\Admin\Models\Account;
use Modules\Admin\Models\NullAccount;

/**
 * News article class.
 *
 * @package Modules\Editor\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class EditorDocHistory implements \JsonSerializable
{
    /**
     * Article ID.
     *
     * @var int
     * @since 1.0.0
     */
    protected int $id = 0;

    /**
     * Doc ID.
     *
     * @var int
     * @since 1.0.0
     */
    public int $doc = 0;

    /**
     * Version.
     *
     * @var string
     * @since 1.0.0
     */
    public string $version = '';

    /**
     * Title.
     *
     * @var string
     * @since 1.0.0
     */
    public string $title = '';

    /**
     * Content.
     *
     * @var string
     * @since 1.0.0
     */
    public string $content = '';

    /**
     * Unparsed.
     *
     * @var string
     * @since 1.0.0
     */
    public string $plain = '';

    /**
     * Created.
     *
     * @var \DateTimeImmutable
     * @since 1.0.0
     */
    public \DateTimeImmutable $createdAt;

    /**
     * Creator.
     *
     * @var Account
     * @since 1.0.0
     */
    public Account $createdBy;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->createdBy = new NullAccount();
        $this->createdAt = new \DateTimeImmutable('now');
    }

    /**
     * Create history form model
     *
     * @param EditorDoc $doc Document
     *
     * @return self
     *
     * @since 1.0.0
     */
    public static function createFromDoc(EditorDoc $doc) : self
    {
        $hist            = new self();
        $hist->doc       = $doc->getId();
        $hist->createdBy = $doc->createdBy;
        $hist->title     = $doc->title;
        $hist->plain     = $doc->plain;
        $hist->content   = $doc->content;
        $hist->version   = $doc->version;

        return $hist;
    }

    /**
     * Get the id
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'        => $this->id,
            'title'     => $this->title,
            'plain'     => $this->plain,
            'content'   => $this->content,
            'createdAt' => $this->createdAt,
            'createdBy' => $this->createdBy,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) \json_encode($this->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize() : mixed
    {
        return $this->toArray();
    }
}
