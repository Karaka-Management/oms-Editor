<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

use Modules\Editor\Controller\ApiController;
use Modules\Editor\Models\PermissionCategory;
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
                'state'  => PermissionCategory::DOC,
            ],
        ],
        [
            'dest'       => '\Modules\Editor\Controller\ApiController:apiEditorUpdate',
            'verb'       => RouteVerb::SET,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::MODIFY,
                'state'  => PermissionCategory::DOC,
            ],
        ],
        [
            'dest'       => '\Modules\Editor\Controller\ApiController:apiEditorGet',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::DOC,
            ],
        ],
        [
            'dest'       => '\Modules\Editor\Controller\ApiController:apiEditorDelete',
            'verb'       => RouteVerb::DELETE,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::DELETE,
                'state'  => PermissionCategory::DOC,
            ],
        ],
    ],
];
