<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Editor
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Editor\Theme\Backend\Components\Compound;

use phpOMS\Localization\L11nManager;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Views\View;

/**
 * Component view.
 *
 * @package Modules\Editor
 * @license OMS License 2.2
 * @link    https://jingga.app
 * @since   1.0.0
 * @codeCoverageIgnore
 */
class BaseView extends View
{
    /**
     * Form id
     *
     * @var string
     * @since 1.0.0
     */
    public string $form = '';

    /**
     * Virtual path of the media file
     *
     * @var string
     * @since 1.0.0
     */
    public string $virtualPath = '';

    /**
     * Name of the image preview
     *
     * @var string
     * @since 1.0.0
     */
    public string $name = '';

    /**
     * Media files
     *
     * @var \Modules\Media\Models\Media[]
     * @since 1.0.0
     */
    public array $docs = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(L11nManager $l11n, RequestAbstract $request, ResponseAbstract $response)
    {
        parent::__construct($l11n, $request, $response);
        $this->setTemplate('/Modules/Editor/Theme/Backend/Components/Compound/compound');

        $this->data['editor'] = new \Modules\Editor\Theme\Backend\Components\Editor\BaseView($l11n, $request, $response);
    }

    /**
     * {@inheritdoc}
     */
    public function render(mixed ...$data) : string
    {
        /** @var array{0:string, 1?:string, 2?:array} $data */
        $this->form        = $data[0];
        $this->virtualPath = $data[1] ?? $this->virtualPath;
        $this->docs        = $data[2] ?? $this->docs;

        return parent::render();
    }
}
