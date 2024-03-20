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
 */
declare(strict_types=1);

use phpOMS\Uri\UriFactory;

?>
<div class="row">
    <div class="col-xs-12 col-md-6">
        <div class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Note', 'Editor', 'Backend'); ?></div>
            <form id="<?= $this->form; ?>-create">
                <div class="portlet-body">
                    <div class="form-group">
                        <label for="iNoteTitle"><?= $this->getHtml('Title', 'Editor', 'Backend'); ?></label>
                        <input type="text" id="iNoteTitle" name="note_title" value="<?= $this->printHtml(''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="iNoteContent"><?= $this->getHtml('Content', 'Editor', 'Backend'); ?></label>
                        <pre id="iNoteContent" class="textarea contenteditable" name="description" contenteditable="true"><?= $this->printHtml(''); ?></pre>
                    </div>
                </div>
            </form>
        </div>

        <section class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Notes', 'Editor', 'Backend'); ?><i class="g-icon download btn end-xs">download</i></div>
                <div class="slider">
                <table id="iNotes" class="default sticky">
                    <thead>
                        <tr>
                            <td>
                            <td><?= $this->getHtml('ID', '0', '0'); ?><i class="sort-asc g-icon">expand_less</i><i class="sort-desc g-icon">expand_more</i>
                            <td class="wf-100"><?= $this->getHtml('Name', 'Editor', 'Backend'); ?><i class="sort-asc g-icon">expand_less</i><i class="sort-desc g-icon">expand_more</i>
                            <td>
                    <tbody
                        id="iNoteInput-tags"
                        class="tags"
                        data-action='[{"listener": "change", "action": [{"key": 1, "type": "dom.set", "selector": "#iNoteNote", "value": "<?= UriFactory::build('{/api}editor/export') . '?id={!#iNotes [name=doc_doc]:checked}&type=html'; ?>"}]}]'
                        data-limit="0"
                        data-active="true"
                        data-form="<?= $this->form; ?>"
                    >
                        <template id="iNoteInput-tagTemplate">
                            <tr data-tpl-value="/id" data-value="" data-uuid="" data-name="doc-list">
                                <td><label class="radio" for="iNote-0">
                                        <input id="iNote-0" type="radio" name="doc_doc" value="">
                                        <span class="checkmark"></span>
                                    </label>
                                <td data-tpl-text="/id" data-tpl-value="/id" data-value=""></td>
                                <td data-tpl-text="/name" data-tpl-value="/name" data-value=""></td>
                                <td>
                        </template>
                        <?php
                        $count = 0;
                        foreach ($this->docs as $doc) :
                            ++$count;
                            $url = UriFactory::build('{/base}/editor/view?{?}&id=' . $doc->id);
                        ?>
                            <tr data-tpl-value="/id" data-value="" data-uuid="" data-name="doc-list">
                                <td><label class="radio" for="iNote-<?= $doc->id; ?>">
                                        <input id="iNote-<?= $doc->id; ?>" type="radio" name="doc_doc" value="<?= $doc->id; ?>"<?= \end($this->docs)->id === $doc->id ? ' checked' : ''; ?>>
                                        <span class="checkmark"></span>
                                    </label>
                                <td data-tpl-text="/id" data-tpl-value="/id" data-value=""><?= $this->printHtml((string) $doc->id); ?></td>
                                <td data-tpl-text="/name" data-tpl-value="/name" data-value=""><?= $this->printHtml($doc->title); ?></td>
                                <td><a href="<?= $url; ?>"><i class="g-icon">attachment</i></a>
                        <?php endforeach; ?>
                        <?php if ($count === 0) : ?>
                            <tr><td colspan="4" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
                        <?php endif; ?>
                </table>
            </div>
        </section>
    </div>

    <div class="col-xs-12 col-md-6 col-simple">
        <section id="docNote" class="portlet col-simple">
            <div class="portlet-body col-simple">
                <?php if (!empty($this->docs)) : ?>
                    <iframe class="col-simple" id="iNoteNote" data-src="<?= UriFactory::build('{/api}editor/export') . '?id={!#iNotes [name=doc_doc]:checked}&type=html'; ?>" allowfullscreen></iframe>
                <?php else : ?>
                    <img width="100%" src="Web/Backend/img/logo_grey.png">
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>
