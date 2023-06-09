<div class="row">
    <div class="col-xs-12 col-md-6">
        <div class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Editor', 'Editor', 'Backend'); ?></div>
            <div class="portlet-body">
                <input name="title" type="text" class="wf-100" value="">
                <div class="form-group">
                    <?= $this->data['editor']->render('note-editor'); ?>
                </div>

                <div class="form-group">
                    <?= $this->data['editor']->getData('text')->render('note-editor', 'plain', 'fNote'); ?>
                </div>
            </div>
            <div class="portlet-foot">
                <input id="bAttributeAdd" formmethod="put" type="submit" class="add-form" value="<?= $this->getHtml('Add', '0', '0'); ?>">
                <input id="bAttributeSave" formmethod="post" type="submit" class="save-form hidden button save" value="<?= $this->getHtml('Update', '0', '0'); ?>">
                <input type="submit" class="cancel-form hidden button close" value="<?= $this->getHtml('Cancel', '0', '0'); ?>">
            </div>
        </div>
    </div>

    <div class="col-xs-12 col-md-6">
        <section class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Notes', 'Editor', 'Backend'); ?><i class="lni lni-download download btn end-xs"></i></div>
                <div class="slider">
                <table class="default">
                    <thead>
                        <tr>
                            <td>
                            <td><?= $this->getHtml('ID', '0', '0'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                            <td class="wf-100"><?= $this->getHtml('Title', 'Editor', 'Backend'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                    <tbody id="iEditorInput-tags" class="tags" data-limit="0" data-active="true" data-form="<?= $this->form; ?>">
                        <template id="iEditorInput-tagTemplate">
                            <tr data-tpl-value="/id" data-value="" data-uuid="" data-name="editor-list">
                                <td>
                                <td data-tpl-text="/id" data-tpl-value="/id" data-value=""></td>
                                <td data-tpl-text="/title" data-tpl-value="/title" data-value=""></td>
                        </template>
                        <?php foreach ($this->files as $file) : ?>
                            <tr data-tpl-value="/id" data-value="" data-uuid="" data-name="editor-list">
                                <td>
                                <td data-tpl-text="/id" data-tpl-value="/id" data-value=""><?= $this->printHtml($file->id); ?></td>
                                <td data-tpl-text="/title" data-tpl-value="/title" data-value=""><?= $this->printHtml($file->name); ?></td>
                        <?php endforeach; ?>
                </table>
            </div>
        </desction>
    </div>
</div>