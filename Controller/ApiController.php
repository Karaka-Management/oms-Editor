<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\Editor
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Editor\Controller;

use Modules\Admin\Models\AccountMapper;
use Modules\Admin\Models\NullAccount;
use Modules\Editor\Models\EditorDoc;
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
use phpOMS\Message\NotificationLevel;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Model\Message\FormValidation;
use phpOMS\Utils\Parser\Markdown\Markdown;

/**
 * Calendar controller class.
 *
 * @package Modules\Editor
 * @license OMS License 1.0
 * @link    https://orange-management.org
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
        if (($val['title'] = empty($request->getData('title')))
            || ($val['plain'] = empty($request->getData('plain')))
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
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiEditorCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!empty($val = $this->validateEditorCreate($request))) {
            $response->set('editor_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $doc = $this->createDocFromRequest($request);
        $this->createModel($request->header->account, $doc, EditorDocMapper::class, 'doc', $request->getOrigin());

        if (!empty($request->getFiles() ?? [])
            || !empty($request->getDataJson('media') ?? [])
        ) {
            $this->createDocMedia($doc, $request);
        }

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Document', 'Document successfully created', $doc);
    }

    private function createDocMedia(EditorDoc $doc, RequestAbstract $request) : void
    {
        $path = $this->createEditorDir($doc);
        $account = AccountMapper::get()->where('id', $request->header->account)->execute();

        if (!empty($uploadedFiles = $request->getFiles() ?? [])) {
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
                MediaMapper::create()->execute($media);
                EditorDocMapper::writer()->createRelationTable('media', [$media->getId()], $doc->getId());

                $ref = new Reference();
                $ref->source = new NullMedia($media->getId());
                $ref->createdBy = new NullAccount($request->header->account);
                $ref->setVirtualPath($accountPath = '/Accounts/' . $account->getId() . ' ' . $account->login . '/Editor/' . $doc->createdAt->format('Y') . '/' . $doc->createdAt->format('m') . '/' . $doc->getId());

                ReferenceMapper::create()->execute($ref);

                if ($collection === null) {
                    $collection = $this->app->moduleManager->get('Media')->createRecursiveMediaCollection(
                        '/Modules/Media/Files',
                        $accountPath,
                        $request->header->account,
                        __DIR__ . '/../../../Modules/Media/Files/Accounts/' . $account->getId() . '/Editor/' . $doc->createdAt->format('Y') . '/' . $doc->createdAt->format('m') . '/' . $doc->getId()
                    );
                }

                CollectionMapper::writer()->createRelationTable('sources', [$ref->getId()], $collection->getId());
            }
        }

        if (!empty($mediaFiles = $request->getDataJson('media') ?? [])) {
            $collection = null;

            foreach ($mediaFiles as $media) {
                EditorDocMapper::writer()->createRelationTable('media', [(int) $media], $doc->getId());

                $ref = new Reference();
                $ref->source = new NullMedia((int) $media);
                $ref->createdBy = new NullAccount($request->header->account);
                $ref->setVirtualPath($path);

                ReferenceMapper::create()->execute($ref);

                if ($collection === null) {
                    $collection = $this->app->moduleManager->get('Media')->createRecursiveMediaCollection(
                        '/Modules/Media/Files',
                        $path,
                        $request->header->account,
                        __DIR__ . '/../../../Modules/Media/Files' . $path
                    );
                }

                CollectionMapper::writer()->createRelationTable('sources', [$ref->getId()], $collection->getId());
            }
        }
    }

    private function createEditorDir(EditorDoc $doc) : string
    {
        return '/Modules/Editor/'
            . $doc->createdAt->format('Y') . '/'
            . $doc->createdAt->format('m') . '/'
            . $doc->createdAt->format('d') . '/'
            . $doc->getId();
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
        $doc          = new EditorDoc();
        $doc->title   = (string) ($request->getData('title') ?? '');
        $doc->plain   = (string) ($request->getData('plain') ?? '');
        $doc->content = Markdown::parse((string) ($request->getData('plain') ?? ''));
        $doc->setVirtualPath((string) ($request->getData('virtualpath') ?? '/'));
        $doc->createdBy = new NullAccount($request->header->account);

        if (!empty($tags = $request->getDataJson('tags'))) {
            foreach ($tags as $tag) {
                if (!isset($tag['id'])) {
                    $request->setData('title', $tag['title'], true);
                    $request->setData('color', $tag['color'], true);
                    $request->setData('icon', $tag['icon'] ?? null, true);
                    $request->setData('language', $tag['language'], true);

                    $internalResponse = new HttpResponse();
                    $this->app->moduleManager->get('Tag')->apiTagCreate($request, $internalResponse, null);
                    $doc->addTag($internalResponse->get($request->uri->__toString())['response']);
                } else {
                    $doc->addTag(new NullTag((int) $tag['id']));
                }
            }
        }

        return $doc;
    }

    /**
     * Api method to create document
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiEditorUpdate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        /** @var \Modules\Editor\Models\EditorDoc $old */
        $old = clone EditorDocMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $new = $this->updateEditorFromRequest($request);
        $this->updateModel($request->header->account, $old, $new, EditorDocMapper::class, 'doc', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Document', 'Document successfully updated', $new);
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
        $doc          = EditorDocMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $doc->title   = (string) ($request->getData('title') ?? $doc->title);
        $doc->plain   = (string) ($request->getData('plain') ?? $doc->plain);
        $doc->content = Markdown::parse((string) ($request->getData('plain') ?? $doc->plain));

        return $doc;
    }

    /**
     * Api method to get a document
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiEditorGet(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        /** @var \Modules\Editor\Models\EditorDoc $doc */
        $doc = EditorDocMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Document', 'Document successfully returned', $doc);
    }

    /**
     * Api method to delete document
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiEditorDelete(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        /** @var \Modules\Editor\Models\EditorDoc $doc */
        $doc = EditorDocMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $this->deleteModel($request->header->account, $doc, EditorDocMapper::class, 'doc', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Document', 'Document successfully deleted', $doc);
    }

    /**
     * Api method to create files
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiFileCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!empty($val = $this->validateEditorFileCreate($request))) {
            $response->set('file_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $uploadedFiles = $request->getFiles() ?? [];

        if (empty($uploadedFiles)) {
            $this->fillJsonResponse($request, $response, NotificationLevel::ERROR, 'Editor', 'Invalid file', $uploadedFiles);
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $uploaded = $this->app->moduleManager->get('Media')->uploadFiles(
            $request->getDataList('names') ?? [],
            $request->getDataList('filenames') ?? [],
            $uploadedFiles,
            $request->header->account,
            __DIR__ . '/../../../Modules/Media/Files/Modules/Editor/' . ($request->getData('doc') ?? '0'),
            '/Modules/Editor/' . ($request->getData('doc') ?? '0'),
            $request->getData('type', 'int'),
            '',
            '',
            PathSettings::FILE_PATH
        );

        $this->createModelRelation(
            $request->header->account,
            (int) $request->getData('doc'),
            \reset($uploaded)->getId(),
            EditorDocMapper::class, 'media', '', $request->getOrigin()
        );

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'File', 'File successfully updated', $uploaded);
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
        if (($val['doc'] = empty($request->getData('doc')))) {
            return $val;
        }

        return [];
    }
}
