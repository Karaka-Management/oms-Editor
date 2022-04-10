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

use Modules\Admin\Models\AccountMapper;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Editor doc mapper class.
 *
 * @package Modules\Editor\Models
 * @license OMS License 1.0
 * @link    https://karaka.app
 * @since   1.0.0
 */
final class EditorDocHistoryMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'editor_doc_versioned_id'           => ['name' => 'editor_doc_versioned_id',         'type' => 'int',               'internal' => 'id'],
        'editor_doc_versioned_version'      => ['name' => 'editor_doc_versioned_version',      'type' => 'string',            'internal' => 'version'],
        'editor_doc_versioned_title'        => ['name' => 'editor_doc_versioned_title',      'type' => 'string',            'internal' => 'title'],
        'editor_doc_versioned_plain'        => ['name' => 'editor_doc_versioned_plain',      'type' => 'string',            'internal' => 'plain'],
        'editor_doc_versioned_content'      => ['name' => 'editor_doc_versioned_content',    'type' => 'string',            'internal' => 'content'],
        'editor_doc_versioned_at'           => ['name' => 'editor_doc_versioned_at', 'type' => 'DateTimeImmutable', 'internal' => 'createdAt', 'readonly' => true],
        'editor_doc_versioned_by'           => ['name' => 'editor_doc_versioned_by', 'type' => 'int',               'internal' => 'createdBy', 'readonly' => true],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:string, external:string}>
     * @since 1.0.0
     */
    public const BELONGS_TO = [
        'createdBy' => [
            'mapper'   => AccountMapper::class,
            'external' => 'editor_doc_versioned_by',
        ],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'editor_doc_versioned';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD ='editor_doc_versioned_id';

    /**
     * Created at.
     *
     * @var string
     * @since 1.0.0
     */
    public const AT = 'editor_doc_versioned_at';
}
