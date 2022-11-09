<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Editor\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Editor\Models;

/**
 * Null model
 *
 * @package Modules\Editor\Models
 * @license OMS License 1.0
 * @link    https://karaka.app
 * @since   1.0.0
 */
final class NullEditorDocTypeL11n extends EditorDocTypeL11n
{
    /**
     * Constructor
     *
     * @param int $id Model id
     *
     * @since 1.0.0
     */
    public function __construct(int $id = 0)
    {
        $this->id = $id;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize() : mixed
    {
        return ['id' => $this->id];
    }
}
