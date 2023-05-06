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
use Modules\Media\Models\Media;
use Modules\Tag\Models\Tag;

/**
 * News article class.
 *
 * @package Modules\Editor\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class EditorDoc implements \JsonSerializable
{
    /**
     * Article ID.
     *
     * @var int
     * @since 1.0.0
     */
    public int $id = 0;

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
     * Type.
     *
     * @var null|int|EditorDocType
     * @since 1.0.0
     */
    public null | int | EditorDocType $type = null;

    /**
     * Doc path for organizing.
     *
     * @var string
     * @since 1.0.0
     */
    private string $virtualPath = '/';

    /**
     * Doc is visible in editor doc list.
     *
     * @var bool
     * @since 1.0.0
     */
    public bool $isVisible = true;

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
     * Tags.
     *
     * @var Tag[]
     * @since 1.0.0
     */
    private array $tags = [];

    /**
     * Media files
     *
     * @var Media[]
     * @since 1.0.0
     */
    protected array $media = [];

    /**
     * Is versioned
     *
     * @var bool
     * @since 1.0.0
     */
    public bool $isVersioned = false;

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
     * Get the path
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getVirtualPath() : string
    {
        return $this->virtualPath;
    }

    /**
     * Set the path if file
     *
     * @param string $path Path to file
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function setVirtualPath(string $path)
    {
        $this->virtualPath = $path;
    }

    /**
     * Get tags
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function getTags() : array
    {
        return $this->tags;
    }

    /**
     * Add tag
     *
     * @param Tag $tag Tag
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function addTag(Tag $tag) : void
    {
        $this->tags[] = $tag;
    }

    /**
     * Get all media
     *
     * @return Media[]
     *
     * @since 1.0.0
     */
    public function getMedia() : array
    {
        return $this->media;
    }

    /**
     * Add media
     *
     * @param Media $media Media to add
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function addMedia(Media $media) : void
    {
        $this->media[] = $media;
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
