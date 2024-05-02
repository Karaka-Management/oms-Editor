<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Editor
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 *
 * @todo Create immediate text preview similar to a rich text editor or Typora
 *      https://github.com/Karaka-Management/oms-Editor/issues/4
 */
declare(strict_types=1);
?>
<div id="<?= $this->renderId(); ?>" class="tabview tab-2 m-editor wf-100">
    <ul class="tab-links">
        <li><label tabindex="0" for="<?= $this->renderId(); ?>-c-tab-1"><?= $this->getHtml('Text', 'Editor'); ?></label>
        <li<?= empty($this->plain) ? '' : ' class="active"'; ?>><label tabindex="0" for="<?= $this->renderId(); ?>-c-tab-2"><?= $this->getHtml('Preview', 'Editor'); ?></label>
    </ul>
    <div class="tab-content">
        <input type="radio" id="<?= $this->renderId(); ?>-c-tab-1" name="tabular-1"<?= empty($this->plain) ? ' checked' : ''; ?>>
        <div class="tab">
            <textarea
                tabindex="0"
                id="i<?= $this->renderName(); ?>"
                style="height: 200px"
                name="<?= $this->renderName(); ?>"
                form="<?= $this->renderForm(); ?>"
                data-tpl-text="<?= $this->renderTplText(); ?>"
                data-tpl-value="<?= $this->renderTplValue(); ?>"><?= \str_replace(["\r\n", "\n"], ['&#10;', '&#10;'], $this->renderPlain()); ?></textarea>
            <input type="hidden" id="<?= $this->renderId(); ?>-parsed">
        </div>

        <input type="radio" id="<?= $this->renderId(); ?>-c-tab-2" name="tabular-1"<?= empty($this->plain) ? '' : ' checked'; ?>>
        <div class="tab">
            <article data-tpl-text="<?= $this->renderTplText(); ?>" data-tpl-value="<?= $this->renderTplValue(); ?>"><?= $this->renderPreview(); ?></article>
        </div>
    </div>
</div>
