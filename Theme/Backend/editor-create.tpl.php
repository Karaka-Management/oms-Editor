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

use Modules\Editor\Models\NullEditorDoc;
use phpOMS\Uri\UriFactory;

/** @var \Modules\Editor\Models\EditorDoc $doc */
$doc      = $this->getData('doc') ?? new NullEditorDoc();
$isNewDoc = $doc->id === 0;

/** @var \phpOMS\Views\View $this */
echo $this->data['nav']->render(); ?>

<div class="row">
    <div class="col-xs-12 col-md-8">
        <section class="portlet">
            <div class="portlet-body">
                <form id="fEditor"
                    method="<?= $isNewDoc ? 'PUT' : 'POST'; ?>"
                    action="<?= UriFactory::build('{/api}editor?{?}&csrf={$CSRF}'); ?>"
                    <?= $isNewDoc ? 'data-redirect="' . UriFactory::build('{/base}/editor/view') . '?id={/0/response/id}"' : ''; ?>>
                    <div class="ipt-wrap">
                        <div class="ipt-first"><input name="title" type="text" class="wf-100" value="<?= $doc->title; ?>"></div>
                        <div class="ipt-second"><input type="submit" value="<?= $this->getHtml('Save'); ?>" name="save-editor"></div>
                    </div>
                </form>
            </div>
        </section>

        <section class="portlet">
            <div class="portlet-body">
                <?= $this->getData('editor')->render('editor'); ?>
            </div>
        </section>

        <section class="portlet">
            <div class="portlet-body">
                <?= $this->getData('editor')->getData('text')->render(
                    'editor',
                    'plain',
                    'fEditor',
                    $doc->plain,
                    $doc->content
                ); ?>
            </div>
        </section>
    </div>

    <div class="col-xs-12 col-md-4">
        <section class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Tags', 'Tag'); ?></div>
            <div class="portlet-body">
                <?= $this->getData('tagSelector')->render('iTag', 'tag', 'fEditor', false); ?>
            </div>
        </section>

        <section class="portlet">
            <div class="portlet-body">
                <form>
                    <table class="layout">
                        <tr><td colspan="2"><label><?= $this->getHtml('Permission'); ?></label>
                        <tr><td><select name="permission">
                                    <option>
                                </select>
                        <tr><td colspan="2"><label><?= $this->getHtml('GroupUser'); ?></label>
                        <tr><td><input id="iPermission" name="group" type="text"><td><button><?= $this->getHtml('Add'); ?></button>
                    </table>
                </form>
            </div>
        </section>
    </div>
</div>
