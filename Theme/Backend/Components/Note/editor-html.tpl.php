<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Template
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use phpOMS\Utils\Parser\Markdown\Markdown;

?>
<article><?= Markdown::parse($this->data['doc']->plain); ?></article>
