<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\Editor\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Editor\Models;

use phpOMS\DataStorage\Database\DataMapperAbstract;

/**
 * Editor type l11n mapper class.
 *
 * @package Modules\Editor\Models
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
final class EditorDocTypeL11nMapper extends DataMapperAbstract
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    protected static array $columns = [
        'editor_doc_type_l11n_id'        => ['name' => 'editor_doc_type_l11n_id',       'type' => 'int',    'internal' => 'id'],
        'editor_doc_type_l11n_title'     => ['name' => 'editor_doc_type_l11n_title',    'type' => 'string', 'internal' => 'title', 'autocomplete' => true],
        'editor_doc_type_l11n_type'      => ['name' => 'editor_doc_type_l11n_type',      'type' => 'int',    'internal' => 'type'],
        'editor_doc_type_l11n_language'  => ['name' => 'editor_doc_type_l11n_language', 'type' => 'string', 'internal' => 'language'],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    protected static string $table = 'editor_doc_type_l11n';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    protected static string $primaryField = 'editor_doc_type_l11n_id';
}
