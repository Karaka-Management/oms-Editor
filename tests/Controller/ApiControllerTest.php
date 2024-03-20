<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Editor\tests\Controller;

use Model\CoreSettings;
use Modules\Admin\Models\AccountPermission;
use Modules\Admin\Models\NullAccount;
use Modules\Editor\Controller\ApiController;
use Modules\Editor\Models\EditorDoc;
use Modules\Editor\Models\EditorDocMapper;
use Modules\Editor\Models\NullEditorDoc;
use phpOMS\Account\Account;
use phpOMS\Account\AccountManager;
use phpOMS\Account\PermissionType;
use phpOMS\Application\ApplicationAbstract;
use phpOMS\Dispatcher\Dispatcher;
use phpOMS\Event\EventManager;
use phpOMS\Localization\L11nManager;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Module\ModuleManager;
use phpOMS\Router\WebRouter;
use phpOMS\System\MimeType;
use phpOMS\Utils\TestUtils;

/**
 * @internal
 */
final class ApiControllerTest extends \PHPUnit\Framework\TestCase
{
    protected ApplicationAbstract $app;

    /**
     * @var \Modules\Editor\Controller\ApiController
     */
    protected ApiController $module;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->app = new class() extends ApplicationAbstract
        {
            protected string $appName = 'Api';
        };

        $this->app->dbPool         = $GLOBALS['dbpool'];
        $this->app->unitId         = 1;
        $this->app->accountManager = new AccountManager($GLOBALS['session']);
        $this->app->appSettings    = new CoreSettings();
        $this->app->moduleManager  = new ModuleManager($this->app, __DIR__ . '/../../../Modules/');
        $this->app->dispatcher     = new Dispatcher($this->app);
        $this->app->eventManager   = new EventManager($this->app->dispatcher);
        $this->app->l11nManager    = new L11nManager();
        $this->app->eventManager->importFromFile(__DIR__ . '/../../../Web/Api/Hooks.php');

        $account = new Account();
        TestUtils::setMember($account, 'id', 1);

        $permission       = new AccountPermission();
        $permission->unit = 1;
        $permission->app  = 2;
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
     * @covers \Modules\Editor\Controller\ApiController
     * @group module
     */
    public function testCreateEditorDoc() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('title', 'Controller Test Title');
        $request->setData('plain', 'Controller Test Description');
        $request->setData('tags', '[{"title": "TestTitle", "color": "#f0f", "language": "en"}, {"id": 1}]');

        if (!\is_file(__DIR__ . '/test_tmp.md')) {
            \copy(__DIR__ . '/test.md', __DIR__ . '/test_tmp.md');
        }

        TestUtils::setMember($request, 'files', [
            'file1' => [
                'name'     => 'test.md',
                'type'     => MimeType::M_TXT,
                'tmp_name' => __DIR__ . '/test_tmp.md',
                'error'    => \UPLOAD_ERR_OK,
                'size'     => \filesize(__DIR__ . '/test_tmp.md'),
            ],
        ]);

        $request->setData('media', \json_encode([1]));

        $this->module->apiEditorCreate($request, $response);

        self::assertEquals('Controller Test Title', $response->getDataArray('')['response']->title);
        self::assertGreaterThan(0, $response->getDataArray('')['response']->id);
    }

    /**
     * @covers \Modules\Editor\Controller\ApiController
     * @group module
     */
    public function testCreateFileForDoc() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('doc', '1');
        $request->setData('name', 'NewUpload');

        if (!\is_file(__DIR__ . '/test_tmp.md')) {
            \copy(__DIR__ . '/test.md', __DIR__ . '/test_tmp.md');
        }

        TestUtils::setMember($request, 'files', [
            'file1' => [
                'name'     => 'test.md',
                'type'     => MimeType::M_TXT,
                'tmp_name' => __DIR__ . '/test_tmp.md',
                'error'    => \UPLOAD_ERR_OK,
                'size'     => \filesize(__DIR__ . '/test_tmp.md'),
            ],
        ]);

        $request->setData('media', \json_encode([1]));

        $this->module->apiFileCreate($request, $response);
        self::assertCount(1, $response->getDataArray('')['response']);
    }

    /**
     * @covers \Modules\Editor\Controller\ApiController
     * @group module
     */
    public function testCreateFileForDocEmptyUpload() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('doc', '1');
        $request->setData('name', 'MissingFile');

        $this->module->apiFileCreate($request, $response);
        self::assertEquals(RequestStatusCode::R_400, $response->header->status);
    }

    /**
     * @covers \Modules\Editor\Controller\ApiController
     * @group module
     */
    public function testCreateFileForDocInvalidData() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('invalid', '1');

        $this->module->apiFileCreate($request, $response);
        self::assertEquals(RequestStatusCode::R_400, $response->header->status);
    }

    /**
     * @covers \Modules\Editor\Controller\ApiController
     * @group module
     */
    public function testInvalidEditorDocCreateRequest() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('title', 'Controller Test Title');

        $this->module->apiEditorCreate($request, $response);
        self::assertEquals(RequestStatusCode::R_400, $response->header->status);
    }

    /**
     * @covers \Modules\Editor\Controller\ApiController
     * @group module
     */
    public function testUpdateEditorDoc() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('id', '1');
        $request->setData('title', 'Changed Title');

        $this->module->apiEditorUpdate($request, $response);

        self::assertEquals('Changed Title', $response->getDataArray('')['response']->title);
        self::assertEquals('Changed Title', EditorDocMapper::get()->where('id', 1)->execute()->title);
        self::assertEquals(1, $response->getDataArray('')['response']->id);
    }

    /**
     * @covers \Modules\Editor\Controller\ApiController
     * @group module
     */
    public function testGetEditorDoc() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('id', '1');

        $this->module->apiEditorGet($request, $response);
        self::assertEquals('Changed Title', $response->getDataArray('')['response']->title);
    }

    /**
     * @covers \Modules\Editor\Controller\ApiController
     * @group module
     */
    public function testDeleteEditorDoc() : void
    {
        $doc            = new EditorDoc();
        $doc->title     = 'TestTitle';
        $doc->content   = 'TestContent';
        $doc->createdBy = new NullAccount(1);

        $docId = EditorDocMapper::create()->execute($doc);

        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('id', $docId);

        $this->module->apiEditorDelete($request, $response);

        self::assertEquals($docId, $response->getDataArray('')['response']->id);
        self::assertInstanceOf(NullEditorDoc::class, EditorDocMapper::get()->where('id', $docId)->execute());
    }
}
