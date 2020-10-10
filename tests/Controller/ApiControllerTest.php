<?php
/**
 * Orange Management
 *
 * PHP Version 7.4
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Editor\tests\Controller;

use Model\CoreSettings;
use Modules\Admin\Models\AccountPermission;
use Modules\Admin\Models\NullAccount;
use Modules\Editor\Controller\ApiController;
use phpOMS\Account\Account;
use phpOMS\Account\AccountManager;
use phpOMS\Account\PermissionType;
use phpOMS\Application\ApplicationAbstract;
use phpOMS\Dispatcher\Dispatcher;
use phpOMS\Event\EventManager;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Module\ModuleManager;
use phpOMS\Router\WebRouter;
use phpOMS\Uri\HttpUri;
use phpOMS\Utils\TestUtils;
use Modules\Editor\Models\EditorDocMapper;
use Modules\Tag\Models\Tag;
use Modules\Tag\Models\TagMapper;
use Modules\Editor\Models\EditorDoc;
use Modules\Editor\Models\NullEditorDoc;

/**
 * @internal
 */
class ApiControllerTest extends \PHPUnit\Framework\TestCase
{
    protected ApplicationAbstract $app;

    /**
     * @var \Modules\Editor\Controller\ApiController
     */
    protected ApiController $module;

    protected function setUp() : void
    {
        $this->app = new class() extends ApplicationAbstract
        {
            protected string $appName = 'Api';
        };

        $this->app->dbPool         = $GLOBALS['dbpool'];
        $this->app->orgId          = 1;
        $this->app->accountManager = new AccountManager($GLOBALS['session']);
        $this->app->appSettings    = new CoreSettings($this->app->dbPool->get());
        $this->app->moduleManager  = new ModuleManager($this->app, __DIR__ . '/../../../Modules');
        $this->app->dispatcher     = new Dispatcher($this->app);
        $this->app->eventManager   = new EventManager($this->app->dispatcher);
        $this->app->eventManager->importFromFile(__DIR__ . '/../../../Web/Api/Hooks.php');

        $account = new Account();
        TestUtils::setMember($account, 'id', 1);

        $permission = new AccountPermission();
        $permission->setUnit(1);
        $permission->setApp('backend');
        $permission->setPermission(
            PermissionType::READ
            | PermissionType::CREATE
            | PermissionType::MODIFY
            | PermissionType::DELETE
            | PermissionType::PERMISSION
        );

        $account->addPermission($permission);

        $this->app->accountManager->add($account);
        $this->app->router = new WebRouter();

        $this->module = $this->app->moduleManager->get('Editor');

        TestUtils::setMember($this->module, 'app', $this->app);
    }

    /**
     * @covers Modules\Editor\Controller\ApiController
     * @group module
     */
    public function testCreateEditorDoc() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->getHeader()->setAccount(1);
        $request->setData('title', 'Controller Test Title');
        $request->setData('plain', 'Controller Test Description');

        $this->module->apiEditorCreate($request, $response);

        self::assertEquals('Controller Test Title', $response->get('')['response']->getTitle());
        self::assertGreaterThan(0, $response->get('')['response']->getId());
    }

    /**
     * @covers Modules\Editor\Controller\ApiController
     * @group module
     */
    public function testCreateEditorDocWithExistingTag() : void
    {
        $tag = new Tag();
        $tag->setTitle('EditorDocTest');
        $tagId = TagMapper::create($tag);

        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->getHeader()->setAccount(1);
        $request->setData('title', 'Controller Test With Tag');
        $request->setData('plain', 'Controller Test Description');
        $request->setData('tag', '[' . $tagId . ']');

        $this->module->apiEditorCreate($request, $response);

        self::assertEquals('Controller Test With Tag', $response->get('')['response']->getTitle());
        self::assertGreaterThan(0, $response->get('')['response']->getId());
    }

    /**
     * @covers Modules\Editor\Controller\ApiController
     * @group module
     */
    public function testInvalidEditorDocCreateRequest() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->getHeader()->setAccount(1);
        $request->setData('title', 'Controller Test Title');

        $this->module->apiEditorCreate($request, $response);

        self::assertEquals(RequestStatusCode::R_400, $response->getHeader()->getStatusCode());
    }

    /**
     * @covers Modules\Editor\Controller\ApiController
     * @group module
     */
    public function testUpdateEditorDoc() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->getHeader()->setAccount(1);
        $request->setData('id', '1');
        $request->setData('title', 'Changed Title');

        $this->module->apiEditorUpdate($request, $response);

        self::assertEquals('Changed Title', $response->get('')['response']->getTitle());
        self::assertEquals('Changed Title', EditorDocMapper::get(1)->getTitle());
        self::assertEquals(1, $response->get('')['response']->getId());
    }

    /**
     * @covers Modules\Editor\Controller\ApiController
     * @group module
     */
    public function testGetEditorDoc() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->getHeader()->setAccount(1);
        $request->setData('id', '1');

        $this->module->apiEditorGet($request, $response);

        self::assertEquals('Changed Title', $response->get('')['response']->getTitle());
    }

    /**
     * @covers Modules\Editor\Controller\ApiController
     * @group module
     */
    public function testDeleteEditorDoc() : void
    {
        $doc = new EditorDoc();
        $doc->setTitle('TestTitle');
        $doc->setContent('TestContent');
        $doc->setCreatedBy(new NullAccount(1));

        $docId = EditorDocMapper::create($doc);

        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->getHeader()->setAccount(1);
        $request->setData('id', $docId);

        $this->module->apiEditorDelete($request, $response);

        self::assertEquals($docId, $response->get('')['response']->getId());
        self::assertInstanceOf(NullEditorDoc::class, EditorDocMapper::get($docId));
    }
}
