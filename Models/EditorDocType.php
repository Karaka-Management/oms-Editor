<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   Modules\Editor\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Editor\Models;

use phpOMS\Contract\ArrayableInterface;
use phpOMS\Localization\ISO639x1Enum;

/**
 * EditorDoc type class.
 *
 * @package Modules\Editor\Models
 * @license OMS License 1.0
 * @link    https://karaka.app
 * @since   1.0.0
 */
class EditorDocType implements \JsonSerializable, ArrayableInterface
{
    /**
     * Article ID.
     *
     * @var int
     * @since 1.0.0
     */
    protected int $id = 0;

    /**
     * Name.
     *
     * Name used for additional identification, doesn't have to be unique.
     *
     * @var string
     * @since 1.0.0
     */
    public string $name = '';

    /**
     * Title.
     *
     * @var string|EditorDocTypeL11n
     * @since 1.0.0
     */
    protected $title;

    /**
     * Constructor.
     *
     * @param string $name Name
     *
     * @since 1.0.0
     */
    public function __construct(string $name = '')
    {
        $this->setL11n($name);
    }

    /**
     * Get id
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
     * @return string
     *
     * @since 1.0.0
     */
    public function getL11n() : string
    {
        return $this->title instanceof EditorDocTypeL11n ? $this->title->title : $this->title;
    }

    /**
     * Set title
     *
     * @param string|EditorDocTypeL11n $title EditorDoc article title
     * @param string                   $lang  Language
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setL11n(string | EditorDocTypeL11n $title, string $lang = ISO639x1Enum::_EN) : void
    {
        if ($title instanceof EditorDocTypeL11n) {
            $this->title = $title;
        } elseif (isset($this->title) && $this->title instanceof EditorDocTypeL11n) {
            $this->title->title = $title;
        } else {
            $this->title        = new EditorDocTypeL11n();
            $this->title->title = $title;
            $this->title->setLanguage($lang);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'    => $this->id,
            'title' => $this->title,
            'name'  => $this->name,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize() : mixed
    {
        return $this->toArray();
    }
}
