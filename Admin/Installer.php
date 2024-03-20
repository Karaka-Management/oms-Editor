<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Editor\Admin
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Editor\Admin;

use phpOMS\Application\ApplicationAbstract;
use phpOMS\Config\SettingsInterface;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Module\InstallerAbstract;
use phpOMS\Module\ModuleInfo;
use phpOMS\System\File\PathException;

/**
 * Installer class.
 *
 * @package Modules\Editor\Admin
 * @license OMS License 2.0
 * @link    https://jingga.app
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
        } catch (\Exception $_) {
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

        $apiApp->dbPool         = $app->dbPool;
        $apiApp->unitId         = $app->unitId;
        $apiApp->accountManager = $app->accountManager;
        $apiApp->appSettings    = $app->appSettings;
        $apiApp->moduleManager  = $app->moduleManager;
        $apiApp->eventManager   = $app->eventManager;

        /** @var array{type:array} $editorData */
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
     * @param array               $data Type info
     *
     * @return array
     *
     * @since 1.0.0
     */
    private static function createType(ApplicationAbstract $app, array $data) : array
    {
        /** @var \Modules\Editor\Controller\ApiDocTypeController $module */
        $module = $app->moduleManager->get('Editor', 'ApiDocType');

        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('name', $data['name'] ?? '');
        $request->setData('title', $data['name'] ?? '');

        $module->apiEditorDocTypeCreate($request, $response);

        $responseData = $response->getData('');
        if (!\is_array($responseData)) {
            return [];
        }

        /** @var \phpOMS\Localization\BaseStringL11nType $type */
        $type = $responseData['response'];
        $id   = $type->id;

        $isFirst = true;
        foreach ($data['l11n'] as $lang => $l11n) {
            if ($isFirst) {
                $isFirst = false;
                continue;
            }

            $response = new HttpResponse();
            $request  = new HttpRequest();

            $request->header->account = 1;
            $request->setData('title', $l11n);
            $request->setData('lang', $lang);
            $request->setData('type', $id);

            $module->apiEditorDocTypeL11nCreate($request, $response);
        }

        return $type->toArray();
    }
}
