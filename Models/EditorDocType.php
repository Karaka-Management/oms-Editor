<?php
/**
 * Jingga
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

use phpOMS\Localization\BaseStringL11n;
use phpOMS\Localization\ISO639x1Enum;

/**
 * EditorDoc type class.
 *
 * @package Modules\Editor\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class EditorDocType implements \JsonSerializable
{
    /**
     * Article ID.
     *
     * @var int
     * @since 1.0.0
     */
    public int $id = 0;

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
     * @var string|BaseStringL11n
     * @since 1.0.0
     */
    protected $title = '';

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
     * @return string
     *
     * @since 1.0.0
     */
    public function getL11n() : string
    {
        return $this->title instanceof BaseStringL11n ? $this->title->content : $this->title;
    }

    /**
     * Set title
     *
     * @param string|BaseStringL11n $title EditorDoc article title
     * @param string                $lang  Language
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setL11n(string | BaseStringL11n $title, string $lang = ISO639x1Enum::_EN) : void
    {
        if ($title instanceof BaseStringL11n) {
            $this->title = $title;
        } elseif ($this->title instanceof BaseStringL11n) {
            $this->title->content = $title;
        } else {
            $this->title          = new BaseStringL11n();
            $this->title->content = $title;
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
