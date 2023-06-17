<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use Modules\Editor\Controller\BackendController;
use Modules\Editor\Models\PermissionCategory;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^.*/editor/create.*$' => [
        [
            'dest'       => '\Modules\Editor\Controller\BackendController:setUpEditorEditor',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::DOC,
            ],
        ],
        [
            'dest'       => '\Modules\Editor\Controller\BackendController:viewEditorCreate',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::DOC,
            ],
        ],
    ],
    '^.*/editor/list.*$' => [
        [
            'dest'       => '\Modules\Editor\Controller\BackendController:viewEditorList',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::DOC,
            ],
        ],
    ],
    '^.*/editor/single.*$' => [
        [
            'dest'       => '\Modules\Editor\Controller\BackendController:viewEditorSingle',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::DOC,
            ],
        ],
    ],
    '^.*/editor/edit.*$' => [
        [
            'dest'       => '\Modules\Editor\Controller\BackendController:viewEditorEdit',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::MODIFY,
                'state'  => PermissionCategory::DOC,
            ],
        ],
    ],
];
