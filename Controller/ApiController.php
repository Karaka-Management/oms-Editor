<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Editor
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Editor\Controller;

use Modules\Admin\Models\AccountMapper;
use Modules\Admin\Models\NullAccount;
use Modules\Admin\Models\SettingsEnum as AdminSettingsEnum;
use Modules\Editor\Models\EditorDoc;
use Modules\Editor\Models\EditorDocHistory;
use Modules\Editor\Models\EditorDocHistoryMapper;
use Modules\Editor\Models\EditorDocMapper;
use Modules\Editor\Models\PermissionCategory;
use Modules\Media\Models\CollectionMapper;
use Modules\Media\Models\Media;
use Modules\Media\Models\PathSettings;
use phpOMS\Account\PermissionType;
use phpOMS\Asset\AssetType;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Model\Html\Head;
use phpOMS\Security\EncryptionHelper;
use phpOMS\System\MimeType;
use phpOMS\Utils\Parser\Markdown\Markdown;
use phpOMS\Views\View;

/**
 * Calendar controller class.
 *
 * @package Modules\Editor
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class ApiController extends Controller
{
    /**
     * Validate document create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateEditorCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['title'] = !$request->hasData('title'))
            || ($val['plain'] = !$request->hasData('plain'))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to create document
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiEditorCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateEditorCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        $doc = $this->createDocFromRequest($request);
        $this->createModel($request->header->account, $doc, EditorDocMapper::class, 'doc', $request->getOrigin());

        if (!empty($request->files)
        || !empty($request->getDataJson('media'))
        ) {
            $this->createDocMedia($doc, $request);
        }

        if ($doc->isVersioned) {
            $history = $this->createHistory($doc);
            $this->createModel($request->header->account, $history, EditorDocHistoryMapper::class, 'doc_history', $request->getOrigin());
        }

        $this->createStandardCreateResponse($request, $response, $doc);
    }

    /**
     * Create media files for editor document
     *
     * @param EditorDoc       $doc     Editor document
     * @param RequestAbstract $request Request incl. media do upload
     *
     * @return void
     *
     * @since 1.0.0
     */
    private function createDocMedia(EditorDoc $doc, RequestAbstract $request) : void
    {
        $path = $this->createEditorDir($doc);

        /** @var \Modules\Admin\Models\Account $account */
        $account     = AccountMapper::get()->where('id', $request->header->account)->execute();
        $accountPath = '/Accounts/' . $account->id . ' ' . $account->login
            . '/Editor/'
            . $doc->createdAt->format('Y/m')
            . '/' . $doc->id;

        if (!empty($request->files)) {
            $uploaded = $this->app->moduleManager->get('Media', 'Api')->uploadFiles(
                names: [],
                fileNames: [],
                files: $request->files,
                account: $request->header->account,
                basePath: __DIR__ . '/../../../Modules/Media/Files' . $path,
                virtualPath: $path,
                rel: $doc->id,
                mapper: EditorDocMapper::class,
                field: 'files'
            );

            if ($account->id !== 0) {
                $this->app->moduleManager->get('Media', 'Api')->addMediaToCollectionAndModel(
                    account: $request->header->account,
                    files: \array_map(function (Media $media) { return $media->id; }, $uploaded->sources),
                    collectionPath: $accountPath
                );
            }
        }

        if (!empty($media = $request->getDataJson('media'))) {
            $this->app->moduleManager->get('Media', 'Api')->addMediaToCollectionAndModel(
                $request->header->account,
                $media,
                $doc->id,
                EditorDocMapper::class,
                'files',
                $path
            );
        }
    }

    /**
     * Create media directory path
     *
     * @param EditorDoc $doc Doc
     *
     * @return string
     *
     * @since 1.0.0
     */
    private function createEditorDir(EditorDoc $doc) : string
    {
        return '/Modules/Editor/'
            . $doc->createdAt->format('Y/m/d') . '/'
            . $doc->id;
    }

    /**
     * Method to create task from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return EditorDoc
     *
     * @since 1.0.0
     */
    private function createDocFromRequest(RequestAbstract $request) : EditorDoc
    {
        $doc              = new EditorDoc();
        $doc->title       = $request->getDataString('title') ?? '';
        $doc->plain       = $request->getDataString('plain') ?? '';
        $doc->content     = Markdown::parse($request->getDataString('plain') ?? '');
        $doc->isVersioned = $request->getDataBool('versioned') ?? false;
        $doc->createdBy   = new NullAccount($request->header->account);
        $doc->version     = $request->getDataString('version') ?? '';
        $doc->setVirtualPath($request->getDataString('virtualpath') ?? '/');
        $doc->isEncrypted = $request->getDataBool('isencrypted') ?? false;
        $doc->isVisible   = $request->getDataBool('isvisible') ?? true;

        if ($request->getDataBool('isencrypted')
            && !empty($_SERVER['OMS_PRIVATE_KEY_I'] ?? '')
        ) {
            $doc->plain   = EncryptionHelper::encryptShared($doc->plain, $_SERVER['OMS_PRIVATE_KEY_I']);
            $doc->content = EncryptionHelper::encryptShared($doc->content, $_SERVER['OMS_PRIVATE_KEY_I']);
        }

        /*
        if ($request->hasData('tags')) {
            $doc->tags = $this->app->moduleManager->get('Tag', 'Api')->createTagsFromRequest($request);
        }
        */

        return $doc;
    }

    /**
     * Create an editor history version
     *
     * @param EditorDoc $doc Editor document
     *
     * @return EditorDocHistory
     *
     * @since 1.0.0
     */
    private function createHistory(EditorDoc $doc) : EditorDocHistory
    {
        return EditorDocHistory::createFromDoc($doc);
    }

    /**
     * Api method to create document
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiEditorUpdate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        /** @var \Modules\Editor\Models\EditorDoc $old */
        $old = EditorDocMapper::get()
            ->where('id', (int) $request->getData('id'))
            ->execute();

        $new = $this->updateEditorFromRequest($request, clone $old);
        $this->updateModel($request->header->account, $old, $new, EditorDocMapper::class, 'doc', $request->getOrigin());

        if ($new->isVersioned
            && ($old->plain !== $new->plain
                || $old->title !== $new->title
            )
        ) {
            $history = $this->createHistory($new);
            $this->createModel($request->header->account, $history, EditorDocHistoryMapper::class, 'doc_history', $request->getOrigin());
        }

        $this->createStandardUpdateResponse($request, $response, $new);
    }

    /**
     * Method to update document from request.
     *
     * @param RequestAbstract $request Request
     * @param EditorDoc       $doc     Doc
     *
     * @return EditorDoc
     *
     * @since 1.0.0
     */
    private function updateEditorFromRequest(RequestAbstract $request, EditorDoc $doc) : EditorDoc
    {
        $doc->isVersioned = $request->getDataBool('versioned') ?? $doc->isVersioned;
        $doc->title       = $request->getDataString('title') ?? $doc->title;
        $doc->plain       = $request->getDataString('plain') ?? $doc->plain;
        $doc->content     = Markdown::parse($request->getDataString('plain') ?? $doc->plain);
        $doc->version     = $request->getDataString('version') ?? $doc->version;

        return $doc;
    }

    /**
     * Api method to get a document
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiEditorGet(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        /** @var \Modules\Editor\Models\EditorDoc $doc */
        $doc = EditorDocMapper::get()
            ->where('id', (int) $request->getData('id'))
            ->execute();

        $this->createStandardReturnResponse($request, $response, $doc);
    }

    /**
     * Api method to delete document
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiEditorDelete(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        /** @var \Modules\Editor\Models\EditorDoc $doc */
        $doc = EditorDocMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $this->deleteModel($request->header->account, $doc, EditorDocMapper::class, 'doc', $request->getOrigin());
        $this->createStandardDeleteResponse($request, $response, $doc);
    }

    /**
     * Api method to create files
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiFileCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateEditorFileCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidAddResponse($request, $response, $val);

            return;
        }

        if (empty($request->files)) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidAddResponse($request, $response, $request->files);

            return;
        }

        $uploaded = $this->app->moduleManager->get('Media', 'Api')->uploadFiles(
            names: $request->getDataList('names'),
            fileNames: $request->getDataList('filenames'),
            files: $request->files,
            account: $request->header->account,
            basePath: __DIR__ . '/../../../Modules/Media/Files/Modules/Editor/' . ($request->getData('doc') ?? '0'),
            virtualPath: '/Modules/Editor/' . ($request->getData('doc') ?? '0'),
            pathSettings: PathSettings::FILE_PATH,
            tag: $request->getDataInt('tag'),
            rel: (int) $request->getDataInt('doc'),
            mapper: EditorDocMapper::class,
            field: 'files'
        );

        if (empty($uploaded->sources)) {
            $this->createInvalidAddResponse($request, $response, []);

            return;
        }

        $this->createStandardAddResponse($request, $response, $uploaded->sources);
    }

    /**
     * Validate document create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateEditorFileCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['doc'] = !$request->hasData('doc'))) {
            return $val;
        }

        return [];
    }

    /**
     * Export doc
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiEditorExport(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        /** @var \Modules\Editor\Models\EditorDoc $doc */
        $doc = EditorDocMapper::get()
            ->with('files')
            ->where('id', (int) $request->getData('id'))
            ->execute();

        $type  = $request->getDataString('type');
        $mimes = $type === null ? $request->header->get('Content-Type') : [$type];

        foreach ($mimes as $mime) {
            if ($mime === MimeType::M_TEXT) {
                $response->header->set('Content-Type', $mime);
                $response->set('', $doc->plain);

                return;
            } elseif ($mime === MimeType::M_HTML) {
                $response->header->set('Content-Type', $mime);
                $response->set('', Markdown::parse($doc->plain));

                return;
            } elseif ($mime === MimeType::M_PDF) {
                $response->header->set('Content-Type', $mime);

                require_once __DIR__ . '/../../../Resources/tcpdf/TCPDF.php';

                $view = new View($this->app->l11nManager, $request, $response);
                $view->setTemplate('/Modules/Editor/Theme/Api/editor-pdf');

                /** @var \Model\Setting[] $settings */
                $settings = $this->app->appSettings->get(null,
                    [
                        AdminSettingsEnum::DEFAULT_TEMPLATES,
                        AdminSettingsEnum::DEFAULT_ASSETS,
                    ],
                    unit: $this->app->unitId,
                    module: 'Admin'
                );

                if (empty($settings)) {
                    /** @var \Model\Setting[] $settings */
                    $settings = $this->app->appSettings->get(null,
                        [
                            AdminSettingsEnum::DEFAULT_TEMPLATES,
                            AdminSettingsEnum::DEFAULT_ASSETS,
                        ],
                        unit: null,
                        module: 'Admin'
                    );
                }

                /** @var \Modules\Media\Models\Collection $defaultTemplates */
                $defaultTemplates = CollectionMapper::get()
                    ->with('sources')
                    ->where('id', (int) $settings[AdminSettingsEnum::DEFAULT_TEMPLATES]->content)
                    ->execute();

                /** @var \Modules\Media\Models\Collection $defaultAssets */
                $defaultAssets = CollectionMapper::get()
                    ->with('sources')
                    ->where('id', (int) $settings[AdminSettingsEnum::DEFAULT_ASSETS]->content)
                    ->execute();

                $view->data['defaultTemplates'] = $defaultTemplates;
                $view->data['defaultAssets']    = $defaultAssets;
                $view->data['pdf']              = $view->render();
                $view->data['doc']              = $doc;

                $response->set('', $view->render());

                return;
            } elseif ($mime === MimeType::M_DOC || $mime === MimeType::M_DOCX) {
                $response->header->set('Content-Type', $mime);

                return;
            }
        }

        $response->header->set('Content-Type', MimeType::M_TEXT);
        $response->set('', $doc->plain);
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param \phpOMS\Message\Http\HttpRequest $request  Request
     * @param HttpResponse                     $response Response
     * @param array                            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiDocExport(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        $doc = null;

        if ($request->hasData('id')) {
            $doc = EditorDocMapper::get()->where('id', (int) $request->getData('id'))->execute();
        } else {
            $response->header->status = RequestStatusCode::R_403;
            $this->createInvalidReturnResponse($request, $response, $doc);

            return;
        }

        if (!($data['ignorePermission'] ?? false)
            && $request->header->account !== $doc->createdBy->id
            && !$this->app->accountManager->get($request->header->account)->hasPermission(
                PermissionType::READ,
                $this->app->unitId,
                $this->app->appId,
                self::NAME,
                PermissionCategory::DOC,
                $doc->id
            )
        ) {
            $response->header->status = RequestStatusCode::R_403;
            $this->createInvalidReturnResponse($request, $response, $doc);

            return;
        }

        $response->header->set('Content-Type', MimeType::M_HTML, true);
        $view               = $this->createView($doc, $request, $response);
        $view->data['path'] = __DIR__ . '/../../../';

        $response->set('export', $view);
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param EditorDoc                        $doc      Media
     * @param \phpOMS\Message\Http\HttpRequest $request  Request
     * @param HttpResponse                     $response Response
     *
     * @return View
     *
     * @todo Implement pdf export
     * @todo Implement spreadsheet (# or ## headline -> new sheet) export
     * @todo Implement word export
     * @todo Implement raw markdown export
     *
     * @since 1.0.0
     */
    public function createView(EditorDoc $doc, RequestAbstract $request, ResponseAbstract $response) : View
    {
        $view              = new View($this->app->l11nManager, $request, $response);
        $view->data['doc'] = $doc;

        $head = new Head();

        $css = '';
        if (\is_file(__DIR__ . '/../../../Web/Backend/css/backend-small.css')) {
            $css = \file_get_contents(__DIR__ . '/../../../Web/Backend/css/backend-small.css');

            if ($css === false) {
                $css = ''; // @codeCoverageIgnore
            }
        }

        $css = \preg_replace('!\s+!', ' ', $css);
        $head->setStyle('core', $css ?? '');

        $head->addAsset(AssetType::CSS, 'cssOMS/styles.css?v=' . self::VERSION);
        $view->data['head'] = $head;

        $view->setTemplate('/Modules/Editor/Theme/Backend/Components/Note/editor-html');

        return $view;
    }
}
