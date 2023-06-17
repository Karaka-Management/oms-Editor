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

use Modules\Admin\Models\AccountMapper;
use Modules\Media\Models\MediaMapper;
use Modules\Tag\Models\TagMapper;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;
use phpOMS\DataStorage\Database\Mapper\ReadMapper;

/**
 * Editor doc mapper class.
 *
 * @package Modules\Editor\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of EditorDoc
 * @extends DataMapperFactory<T>
 */
final class EditorDocMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'editor_doc_id'           => ['name' => 'editor_doc_id',         'type' => 'int',               'internal' => 'id'],
        'editor_doc_version'      => ['name' => 'editor_doc_version',      'type' => 'string',            'internal' => 'version'],
        'editor_doc_title'        => ['name' => 'editor_doc_title',      'type' => 'string',            'internal' => 'title'],
        'editor_doc_plain'        => ['name' => 'editor_doc_plain',      'type' => 'string',            'internal' => 'plain'],
        'editor_doc_content'      => ['name' => 'editor_doc_content',    'type' => 'string',            'internal' => 'content'],
        'editor_doc_type'         => ['name' => 'editor_doc_type',       'type' => 'int',               'internal' => 'type'],
        'editor_doc_virtual'      => ['name' => 'editor_doc_virtual',    'type' => 'string',            'internal' => 'virtualPath'],
        'editor_doc_versioned'    => ['name' => 'editor_doc_versioned',    'type' => 'bool',            'internal' => 'isVersioned'],
        'editor_doc_created_at'   => ['name' => 'editor_doc_created_at', 'type' => 'DateTimeImmutable', 'internal' => 'createdAt'],
        'editor_doc_created_by'   => ['name' => 'editor_doc_created_by', 'type' => 'int',               'internal' => 'createdBy'],
        'editor_doc_visible'      => ['name' => 'editor_doc_visible',    'type' => 'bool',              'internal' => 'isVisible'],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:class-string, external:string, column?:string, by?:string}>
     * @since 1.0.0
     */
    public const BELONGS_TO = [
        'createdBy' => [
            'mapper'   => AccountMapper::class,
            'external' => 'editor_doc_created_by',
        ],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:class-string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    public const OWNS_ONE = [
        'type' => [
            'mapper'   => EditorDocTypeMapper::class,
            'external' => 'editor_doc_type',
        ],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:class-string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'tags' => [
            'mapper'   => TagMapper::class,
            'table'    => 'editor_doc_tag',
            'self'     => 'editor_doc_tag_dst',
            'external' => 'editor_doc_tag_src',
        ],
        'media'        => [
            'mapper'   => MediaMapper::class,
            'table'    => 'editor_doc_media',
            'external' => 'editor_doc_media_dst',
            'self'     => 'editor_doc_media_src',
        ],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'editor_doc';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'editor_doc_id';

    /**
     * Created at.
     *
     * @var string
     * @since 1.0.0
     */
    public const CREATED_AT = 'editor_doc_created_at';

    /**
     * Get editor doc based on virtual path.
     *
     * @param string $virtualPath Virtual path
     * @param int    $account     Account id
     *
     * @return ReadMapper
     *
     * @since 1.0.0
     */
    public static function getByVirtualPath(string $virtualPath = '/', int $account = 0) : ReadMapper
    {
        return self::getAll()
            ->with('createdBy')
            ->with('tags')
            ->with('tags/title')
            ->where('virtualPath', $virtualPath)
            ->where('createdBy', $account);
    }
}
