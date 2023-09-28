<?php
/**
 * Jingga
 *
 * PHP Version 8.1
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
use Modules\Media\Models\CollectionMapper;
use Modules\Media\Models\MediaMapper;
use Modules\Media\Models\NullMedia;
use Modules\Media\Models\PathSettings;
use Modules\Media\Models\Reference;
use Modules\Media\Models\ReferenceMapper;
use Modules\Tag\Models\NullTag;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
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
        $account = AccountMapper::get()->where('id', $request->header->account)->execute();

        if (!empty($uploadedFiles = $request->files)) {
            $uploaded = $this->app->moduleManager->get('Media')->uploadFiles(
                [],
                [],
                $uploadedFiles,
                $request->header->account,
                __DIR__ . '/../../../Modules/Media/Files' . $path,
                $path,
            );

            $collection = null;
            foreach ($uploaded as $media) {
                $accountPath = '/Accounts/' . $account->id . ' ' . $account->login
                    . '/Editor/'
                    . $doc->createdAt->format('Y/m')
                    . '/' . $doc->id;

                if ($collection === null) {
                    $collection = $this->app->moduleManager->get('Media')->createRecursiveMediaCollection(
                        $accountPath,
                        $request->header->account,
                        __DIR__ . '/../../../Modules/Media/Files/Accounts/' . $account->id . '/Editor/' . $doc->createdAt->format('Y/m') . '/' . $doc->id
                    );
                }

                $this->createModelRelation(
                    $request->header->account,
                    $doc->id,
                    $media->id,
                    EditorDocMapper::class,
                    'media',
                    '',
                    $request->getOrigin()
                );

                $ref            = new Reference();
                $ref->name      = $media->name;
                $ref->source    = new NullMedia($media->id);
                $ref->createdBy = new NullAccount($request->header->account);
                $ref->setVirtualPath($accountPath);

                $this->createModel($request->header->account, $ref, ReferenceMapper::class, 'media_reference', $request->getOrigin());

                $this->createModelRelation(
                    $request->header->account,
                    $collection->id,
                    $ref->id,
                    CollectionMapper::class,
                    'sources',
                    '',
                    $request->getOrigin()
                );
            }
        }

        if (!empty($mediaFiles = $request->getDataJson('media'))) {
            $collection = null;
            foreach ($mediaFiles as $media) {
                if ($collection === null) {
                    $collection = $this->app->moduleManager->get('Media')->createRecursiveMediaCollection(
                        $path,
                        $request->header->account,
                        __DIR__ . '/../../../Modules/Media/Files' . $path
                    );
                }

                $this->createModelRelation(
                    $request->header->account,
                    $doc->id,
                    (int) $media,
                    EditorDocMapper::class,
                    'media',
                    '',
                    $request->getOrigin()
                );

                $refMedia = MediaMapper::get()->where('id', $media)->execute();

                $ref            = new Reference();
                $ref->name      = $refMedia->name;
                $ref->source    = new NullMedia((int) $media);
                $ref->createdBy = new NullAccount($request->header->account);
                $ref->setVirtualPath($path);

                $this->createModel($request->header->account, $ref, ReferenceMapper::class, 'media_reference', $request->getOrigin());
                $this->createModelRelation(
                    $request->header->account,
                    $collection->id,
                    $ref->id,
                    CollectionMapper::class,
                    'sources',
                    '',
                    $request->getOrigin()
                );
            }
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

        if (!empty($tags = $request->getDataJson('tags'))) {
            foreach ($tags as $tag) {
                if (!isset($tag['id'])) {
                    $request->setData('title', $tag['title'], true);
                    $request->setData('color', $tag['color'], true);
                    $request->setData('icon', $tag['icon'] ?? null, true);
                    $request->setData('language', $tag['language'], true);

                    $internalResponse = new HttpResponse();
                    $this->app->moduleManager->get('Tag')->apiTagCreate($request, $internalResponse);

                    if (!\is_array($data = $internalResponse->get($request->uri->__toString()))) {
                        continue;
                    }

                    $doc->addTag($data['response']);
                } else {
                    $doc->addTag(new NullTag((int) $tag['id']));
                }
            }
        }

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
        $old = EditorDocMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $old = clone $old;
        $new = $this->updateEditorFromRequest($request);
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
     *
     * @return EditorDoc
     *
     * @since 1.0.0
     */
    private function updateEditorFromRequest(RequestAbstract $request) : EditorDoc
    {
        /** @var \Modules\Editor\Models\EditorDoc $doc */
        $doc              = EditorDocMapper::get()->where('id', (int) $request->getData('id'))->execute();
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
        $doc = EditorDocMapper::get()->where('id', (int) $request->getData('id'))->execute();
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

        $uploadedFiles = $request->files;

        if (empty($uploadedFiles)) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidAddResponse($request, $response, $uploadedFiles);

            return;
        }

        $uploaded = $this->app->moduleManager->get('Media')->uploadFiles(
            names: $request->getDataList('names'),
            fileNames: $request->getDataList('filenames'),
            files: $uploadedFiles,
            account: $request->header->account,
            basePath: __DIR__ . '/../../../Modules/Media/Files/Modules/Editor/' . ($request->getData('doc') ?? '0'),
            virtualPath: '/Modules/Editor/' . ($request->getData('doc') ?? '0'),
            pathSettings: PathSettings::FILE_PATH
        );

        if ($request->hasData('type')) {
            foreach ($uploaded as $file) {
                $this->createModelRelation(
                    $request->header->account,
                    $file->id,
                    $request->getDataInt('type'),
                    MediaMapper::class,
                    'types',
                    '',
                    $request->getOrigin()
                );
            }
        }

        if (empty($uploaded)) {
            $this->createInvalidAddResponse($request, $response, []);

            return;
        }

        $this->createModelRelation(
            $request->header->account,
            (int) $request->getData('doc'),
            \reset($uploaded)->id,
            EditorDocMapper::class, 'media', '', $request->getOrigin()
        );

        $this->createStandardAddResponse($request, $response, $uploaded);
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
            ->with('media')
            ->where('id', (int) $request->getData('id'))
            ->execute();

        $type  = $request->getDataString('type');
        $mimes = $type === null ? $request->header->get('content-type') : [$type];

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

                require_once __DIR__ . '/../../../Resources/tcpdf/tcpdf.php';

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
}
