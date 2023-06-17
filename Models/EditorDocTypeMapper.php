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

use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Editor type mapper class.
 *
 * @package Modules\Editor\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class EditorDocTypeMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'editor_doc_type_id'   => ['name' => 'editor_doc_type_id',   'type' => 'int',    'internal' => 'id'],
        'editor_doc_type_name' => ['name' => 'editor_doc_type_name', 'type' => 'string', 'internal' => 'name'],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:class-string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'title' => [
            'mapper'      => EditorDocTypeL11nMapper::class,
            'table'       => 'editor_doc_type_l11n',
            'self'        => 'editor_doc_type_l11n_type',
            'column'      => 'title',
            'conditional' => true,
            'external'    => null,
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = EditorDocType::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'editor_doc_type';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'editor_doc_type_id';
}
