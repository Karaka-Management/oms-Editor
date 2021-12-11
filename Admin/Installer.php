<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\Editor\Admin
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Editor\Admin;

use Modules\Editor\Models\EditorDocType;
use Modules\Editor\Models\EditorDocTypeL11n;
use Modules\Editor\Models\EditorDocTypeL11nMapper;
use Modules\Editor\Models\EditorDocTypeMapper;
use phpOMS\Application\ApplicationAbstract;
use phpOMS\Config\SettingsInterface;
use phpOMS\DataStorage\Database\DatabasePool;
use phpOMS\Module\InstallerAbstract;
use phpOMS\Module\ModuleInfo;
use phpOMS\System\File\PathException;

/**
 * Installer class.
 *
 * @package Modules\Editor\Admin
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
final class Installer extends InstallerAbstract
{
    /**
     * Path of the file
     *
     * @var string
     * @since 1.0.0
     */
    public const PATH = __DIR__;

    /**
     * {@inheritdoc}
     */
    public static function install(DatabasePool $dbPool, ModuleInfo $info, SettingsInterface $cfgHandler) : void
    {
    	parent::install($dbPool, $info, $cfgHandler);

    	$types = include __DIR__ . '/Install/Types/types.php';
    	foreach ($types as $type) {
    		self::createType($dbPool, $type);
    	}
    }

    /**
     * Install data from providing modules.
     *
     * The data can be either directories which should be created or files which should be "uploaded"
     *
     * @param ApplicationAbstract $app  Application
     * @param array               $data Additional data
     *
     * @return array
     *
     * @throws PathException
     * @throws \Exception
     *
     * @since 1.0.0
     */
    public static function installExternal(ApplicationAbstract $app, array $data) : array
    {
        try {
            $app->dbPool->get()->con->query('select 1 from `editor_doc`');
        } catch (\Exception $e) {
            return []; // @codeCoverageIgnore
        }

        if (!\is_file($data['path'] ?? '')) {
            throw new PathException($data['path'] ?? '');
        }

        $editorFile = \file_get_contents($data['path'] ?? '');
        if ($editorFile === false) {
            throw new PathException($data['path'] ?? ''); // @codeCoverageIgnore
        }

        $editorData = \json_decode($editorFile, true) ?? [];
        if ($editorData === false) {
            throw new \Exception(); // @codeCoverageIgnore
        }

        $result = [
            'type' => [],
        ];

        foreach ($editorData as $editor) {
            switch ($editor['type']) {
                case 'type':
                    $result['type'][] = self::createType($app->dbPool, $editor);
                    break;
                default:
            }
        }

        return $result;
    }

    /**
     * Create type.
     *
     * @param DatabasePool $dbPool Database instance
     * @param array        $data   Type info
     *
     * @return EditorDocType
     *
     * @since 1.0.0
     */
    private static function createType(DatabasePool $dbPool, array $data) : EditorDocType
    {
        $type       = new EditorDocType();
        $type->name = $data['name'] ?? '';

        $id = EditorDocTypeMapper::create()->execute($type);

        foreach ($data['l11n'] as $l11n) {
            $l11n       = new EditorDocTypeL11n($l11n['title'], $l11n['lang']);
            $l11n->type = $id;

            EditorDocTypeL11nMapper::create()->execute($l11n);
        }

        return $type;
    }
}
