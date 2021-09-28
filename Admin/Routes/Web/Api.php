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

use Modules\Editor\Controller\ApiController;
use Modules\Editor\Models\PermissionState;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^.*/editor.*$' => [
        [
            'dest'       => '\Modules\Editor\Controller\ApiController:apiEditorCreate',
            'verb'       => RouteVerb::PUT,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionState::DOC,
            ],
        ],
        [
            'dest'       => '\Modules\Editor\Controller\ApiController:apiEditorUpdate',
            'verb'       => RouteVerb::SET,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::MODIFY,
                'state'  => PermissionState::DOC,
            ],
        ],
        [
            'dest'       => '\Modules\Editor\Controller\ApiController:apiEditorGet',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::DOC,
            ],
        ],
        [
            'dest'       => '\Modules\Editor\Controller\ApiController:apiEditorDelete',
            'verb'       => RouteVerb::DELETE,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::DELETE,
                'state'  => PermissionState::DOC,
            ],
        ],
    ],
];
