<div id="<?= $this->renderId(); ?>" class="tabview tab-2 m-editor">
    <ul class="tab-links">
        <li><label tabindex="0" for="<?= $this->renderId(); ?>-c-tab-1"><?= $this->getHtml('Text', 'Editor'); ?></label>
        <li><label tabindex="0" for="<?= $this->renderId(); ?>-c-tab-2"><?= $this->getHtml('Preview', 'Editor'); ?></label>
    </ul>
    <div class="tab-content">
        <input type="radio" id="<?= $this->renderId(); ?>-c-tab-1" name="tabular-1" checked>
        <div class="tab">
            <textarea
                tabindex="0"
                id="i<?= $this->renderName(); ?>"
                style="height: 300px"
                name="<?= $this->renderName(); ?>"
                form="<?= $this->renderForm(); ?>"
                data-tpl-text="<?= $this->renderTplText(); ?>"
                data-tpl-value="<?= $this->renderTplValue(); ?>"><?= \str_replace("\n", '&#13;&#10;', $this->renderPlain()); ?></textarea>
            <input type="hidden" id="<?= $this->renderId(); ?>-parsed">
        </div>

        <input type="radio" id="<?= $this->renderId(); ?>-c-tab-2" name="tabular-1">
        <div class="tab">
            <article data-tpl-text="<?= $this->renderTplText(); ?>" data-tpl-value="<?= $this->renderTplValue(); ?>"><?= $this->renderPreview(); ?></article>
        </div>
    </div>
</div>