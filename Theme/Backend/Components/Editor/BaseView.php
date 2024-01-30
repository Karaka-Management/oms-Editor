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

namespace Modules\Editor\Theme\Backend\Components\Editor;

use phpOMS\Localization\L11nManager;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Views\View;

/**
 * Component view.
 *
 * @package Modules\Editor
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 * @codeCoverageIgnore
 */
class BaseView extends View
{
    /**
     * Editor id
     *
     * @var string
     * @since 1.0.0
     */
    public string $id = '';

    /**
     * {@inheritdoc}
     */
    public function __construct(L11nManager $l11n, RequestAbstract $request, ResponseAbstract $response)
    {
        parent::__construct($l11n, $request, $response);
        $this->setTemplate('/Modules/Editor/Theme/Backend/Components/Editor/inline-editor-tools');

        $view = new TextView($l11n, $request, $response);
        $this->addData('text', $view);
    }

    /**
     * Render the editor id
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function renderId() : string
    {
        return $this->printHtml($this->id);
    }

    /**
     * {@inheritdoc}
     */
    public function render(mixed ...$data) : string
    {
        $this->id = ($data[0] ?? '') . '-tools';
        return parent::render();
    }
}
