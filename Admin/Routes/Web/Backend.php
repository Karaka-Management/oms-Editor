<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

use Modules\Editor\Controller\BackendController;
use Modules\Editor\Models\PermissionState;
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
                'state'  => PermissionState::DOC,
            ],
        ],
        [
            'dest'       => '\Modules\Editor\Controller\BackendController:viewEditorCreate',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionState::DOC,
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
                'state'  => PermissionState::DOC,
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
                'state'  => PermissionState::DOC,
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
                'state'  => PermissionState::DOC,
            ],
        ],
    ],
];
