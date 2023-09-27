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

use Modules\Editor\Models\EditorDocMapper;
use Modules\Editor\Models\PermissionCategory;
use Modules\Media\Models\CollectionMapper;
use phpOMS\Account\PermissionType;
use phpOMS\Asset\AssetType;
use phpOMS\Contract\RenderableInterface;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Views\View;

/**
 * Calendar controller class.
 *
 * @package Modules\Editor
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 * @codeCoverageIgnore
 */
final class BackendController extends Controller
{
    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function setUpEditorEditor(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        $head  = $response->data['Content']->head;
        $nonce = $this->app->appSettings->getOption('script-nonce');

        $head->addAsset(AssetType::JSLATE, 'Modules/Editor/Controller.js', ['nonce' => $nonce, 'type' => 'module']);
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewEditorCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        $view->setTemplate('/Modules/Editor/Theme/Backend/editor-create');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1005301001, $request, $response);

        $editor               = new \Modules\Editor\Theme\Backend\Components\Editor\BaseView($this->app->l11nManager, $request, $response);
        $view->data['editor'] = $editor;

        $tagSelector               = new \Modules\Tag\Theme\Backend\Components\TagSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->data['tagSelector'] = $tagSelector;

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewEditorList(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        $view->setTemplate('/Modules/Editor/Theme/Backend/editor-list');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1005301001, $request, $response);

        $path = \strtr($request->getDataString('path') ?? '/', '+', ' ');
        $docs = EditorDocMapper::getByVirtualPath($path, $request->header->account)->where('tags/title/language', $response->header->l11n->language)->execute();

        list($collection, $parent) = CollectionMapper::getCollectionsByPath($path);

        $view->data['parent']      = $parent;
        $view->data['collections'] = $collection;
        $view->data['path']        = $path;
        $view->data['docs']        = $docs;
        $view->data['account']     = $this->app->accountManager->get($request->header->account);

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewEditorSingle(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        /** @var \Modules\Editor\Models\EditorDoc $doc */
        $doc = EditorDocMapper::get()
            ->with('tags')
            ->with('tags/title')
            ->with('media')
            ->where('id', (int) $request->getData('id'))
            ->where('tags/title/language', $response->header->l11n->language)
            ->execute();

        $accountId = $request->header->account;

        if ($doc->createdBy->id !== $accountId
            && !$this->app->accountManager->get($accountId)->hasPermission(
                PermissionType::READ, $this->app->unitId, $this->app->appId, self::NAME, PermissionCategory::DOC, $doc->id)
        ) {
            $view->setTemplate('/Web/Backend/Error/403_inline');
            $response->header->status = RequestStatusCode::R_403;
            return $view;
        }

        $view->setTemplate('/Modules/Editor/Theme/Backend/editor-single');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1005301001, $request, $response);
        $view->data['doc'] = $doc;

        $editor               = new \Modules\Editor\Theme\Backend\Components\Editor\BaseView($this->app->l11nManager, $request, $response);
        $view->data['editor'] = $editor;

        $tagSelector               = new \Modules\Tag\Theme\Backend\Components\TagSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->data['tagSelector'] = $tagSelector;

        $view->data['editable'] = $this->app->accountManager->get($accountId)->hasPermission(
            PermissionType::MODIFY, $this->app->unitId, $this->app->appId, self::NAME, PermissionCategory::DOC, $doc->id);

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewEditorEdit(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        /** @var \Modules\Editor\Models\EditorDoc $doc */
        $doc       = EditorDocMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $accountId = $request->header->account;

        if ($doc->createdBy->id !== $accountId
            && !$this->app->accountManager->get($accountId)->hasPermission(
                PermissionType::READ, $this->app->unitId, $this->app->appId, self::NAME, PermissionCategory::DOC, $doc->id)
        ) {
            $view->setTemplate('/Web/Backend/Error/403_inline');
            $response->header->status = RequestStatusCode::R_403;
            return $view;
        }

        $view->setTemplate('/Modules/Editor/Theme/Backend/editor-create');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1005301001, $request, $response);
        $view->data['doc'] = $doc;

        $editor               = new \Modules\Editor\Theme\Backend\Components\Editor\BaseView($this->app->l11nManager, $request, $response);
        $view->data['editor'] = $editor;

        $tagSelector               = new \Modules\Tag\Theme\Backend\Components\TagSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->data['tagSelector'] = $tagSelector;

        return $view;
    }
}
