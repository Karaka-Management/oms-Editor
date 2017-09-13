<div class="tabular">
    <ul class="tab-links">
        <li><label for="c-tab-1"><?= $this->getHtml('Text', 'Editor'); ?></label>
        <li><label for="c-tab-2"><?= $this->getHtml('Preview', 'Editor'); ?></label>
    </ul>
    <div class="tab-content">
        <input type="radio" id="c-tab-1" name="tabular-1" checked>

        <div class="tab">
            <textarea style="height: 300px" placeholder="&#xf040;" name="plain" form="docForm"><?= htmlspecialchars(isset($doc) ? $doc->getPlain() : '', ENT_COMPAT, 'utf-8'); ?></textarea><input type="hidden" id="iParsed" name="parsed">
        </div>
        <input type="radio" id="c-tab-2" name="tabular-1">

        <div class="tab">
            <?= htmlspecialchars(isset($doc) ? $doc->getContent() : '', ENT_COMPAT, 'utf-8'); ?>
        </div>
    </div>
</div>