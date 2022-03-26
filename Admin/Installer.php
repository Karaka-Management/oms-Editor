<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   Modules\Editor\Admin
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
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
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Module\InstallerAbstract;
use phpOMS\Module\ModuleInfo;
use phpOMS\System\File\PathException;
use phpOMS\Uri\HttpUri;

/**
 * Installer class.
 *
 * @package Modules\Editor\Admin
 * @license OMS License 1.0
 * @link    https://karaka.app
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
    public static function install(ApplicationAbstract $app, ModuleInfo $info, SettingsInterface $cfgHandler) : void
    {
        parent::install($app, $info, $cfgHandler);

        $types = include __DIR__ . '/Install/Types/types.php';
        foreach ($types as $type) {
            self::createType($app, $type);
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

        $apiApp = new class() extends ApplicationAbstract
        {
            protected string $appName = 'Api';
        };

        $apiApp->dbPool = $app->dbPool;
        $apiApp->orgId = $app->orgId;
        $apiApp->accountManager = $app->accountManager;
        $apiApp->appSettings = $app->appSettings;
        $apiApp->moduleManager = $app->moduleManager;
        $apiApp->eventManager = $app->eventManager;

        foreach ($editorData as $editor) {
            switch ($editor['type']) {
                case 'type':
                    $result['type'][] = self::createType($apiApp, $editor);
                    break;
                default:
            }
        }

        return $result;
    }

    /**
     * Create type.
     *
     * @param ApplicationAbstract $app  Application
     * @param array        $data   Type info
     *
     * @return EditorDocType
     *
     * @since 1.0.0
     */
    private static function createType(ApplicationAbstract $app, array $data) : EditorDocType
    {
        /** @var \Modules\Editor\Controller\ApiController $module */
        $module = $app->moduleManager->get('Editor');

        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->header->account = 1;
        $request->setData('name', $data['name'] ?? '');

        $module->apiEditorDocTypeCreate($request, $response);

        $type = $response->get('')['response'];
        $id = $type->getId();

        foreach ($data['l11n'] as $l11n) {
            $response = new HttpResponse();
            $request  = new HttpRequest(new HttpUri(''));

            $request->header->account = 1;
            $request->setData('title', $l11n['title'] ?? '');
            $request->setData('lang', $l11n['lang'] ?? null);
            $request->setData('type', $id);

            $module->apiEditorDocTypeL11nCreate($request, $response);
        }

        return $type;
    }
}
