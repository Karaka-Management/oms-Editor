<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Editor
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
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
 * @license OMS License 1.0
 * @link    https://karaka.app
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
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function setUpEditorEditor(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        $head = $response->get('Content')->getData('head');
        $head->addAsset(AssetType::JSLATE, 'Modules/Editor/Controller.js', ['type' => 'module']);
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewEditorCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        $view->setTemplate('/Modules/Editor/Theme/Backend/editor-create');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005301001, $request, $response));

        $editor = new \Modules\Editor\Theme\Backend\Components\Editor\BaseView($this->app->l11nManager, $request, $response);
        $view->addData('editor', $editor);

        $tagSelector = new \Modules\Tag\Theme\Backend\Components\TagSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->addData('tagSelector', $tagSelector);

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewEditorList(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        $view->setTemplate('/Modules/Editor/Theme/Backend/editor-list');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005301001, $request, $response));

        $path = \str_replace('+', ' ', (string) ($request->getData('path') ?? '/'));
        $docs = EditorDocMapper::getByVirtualPath($path, $request->header->account)->where('tags/title/language', $response->getLanguage())->execute();

        list($collection, $parent) = CollectionMapper::getCollectionsByPath($path);

        $view->addData('parent', $parent);
        $view->addData('collections', $collection);
        $view->addData('path', $path);
        $view->addData('docs', $docs);
        $view->addData('account', $this->app->accountManager->get($request->header->account));

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewEditorSingle(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        /** @var \Modules\Editor\Models\EditorDoc $doc */
        $doc = EditorDocMapper::get()
            ->with('tags')
            ->with('tags/title')
            ->with('media')
            ->where('id', (int) $request->getData('id'))
            ->where('tags/title/language', $response->getLanguage())
            ->execute();

        $accountId = $request->header->account;

        if ($doc->createdBy->getId() !== $accountId
            && !$this->app->accountManager->get($accountId)->hasPermission(
                PermissionType::READ, $this->app->orgId, $this->app->appName, self::NAME, PermissionCategory::DOC, $doc->getId())
        ) {
            $view->setTemplate('/Web/Backend/Error/403_inline');
            $response->header->status = RequestStatusCode::R_403;
            return $view;
        }

        $view->setTemplate('/Modules/Editor/Theme/Backend/editor-single');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005301001, $request, $response));
        $view->addData('doc', $doc);

        $editor = new \Modules\Editor\Theme\Backend\Components\Editor\BaseView($this->app->l11nManager, $request, $response);
        $view->addData('editor', $editor);

        $tagSelector = new \Modules\Tag\Theme\Backend\Components\TagSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->addData('tagSelector', $tagSelector);

        $view->addData('editable', $this->app->accountManager->get($accountId)->hasPermission(
            PermissionType::MODIFY, $this->app->orgId, $this->app->appName, self::NAME, PermissionCategory::DOC, $doc->getId())
        );

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewEditorEdit(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        /** @var \Modules\Editor\Models\EditorDoc $doc */
        $doc       = EditorDocMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $accountId = $request->header->account;

        if ($doc->createdBy->getId() !== $accountId
            && !$this->app->accountManager->get($accountId)->hasPermission(
                PermissionType::READ, $this->app->orgId, $this->app->appName, self::NAME, PermissionCategory::DOC, $doc->getId())
        ) {
            $view->setTemplate('/Web/Backend/Error/403_inline');
            $response->header->status = RequestStatusCode::R_403;
            return $view;
        }

        $view->setTemplate('/Modules/Editor/Theme/Backend/editor-create');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1005301001, $request, $response));
        $view->addData('doc', $doc);

        $editor = new \Modules\Editor\Theme\Backend\Components\Editor\BaseView($this->app->l11nManager, $request, $response);
        $view->addData('editor', $editor);

        $tagSelector = new \Modules\Tag\Theme\Backend\Components\TagSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->addData('tagSelector', $tagSelector);

        return $view;
    }
}
