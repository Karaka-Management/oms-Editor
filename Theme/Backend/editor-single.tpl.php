<?php

/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Editor
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

use phpOMS\Uri\UriFactory;

/** @var \Modules\Editor\Models\EditorDoc $doc */
$doc   = $this->getData('doc');
$files = $doc->getMedia();

/** @var bool $editable */
$editable = $this->getData('editable');

/** @var \Modules\Tag\Models\Tag[] $tag */
$tags = $doc->getTags();

/** @var \phpOMS\Views\View $this */
echo $this->getData('nav')->render(); ?>
<div class="row">
    <div class="col-xs-12">
        <section class="portlet">
            <div class="portlet-head"><?= $this->printHtml($doc->title); ?></div>
            <div calss="portlet-body">
            <article>
                <?= $doc->content; ?>
            </article>
            <?php if ($editable || !empty($tags)) : ?>
            <div class="portlet-foot">
                <div class="row">
                    <div class="col-xs-6 overflowfix">
                        <?php foreach ($tags as $tag) : ?>
                            <span class="tag" style="background: <?= $this->printHtml($tag->color); ?>"><?= $tag->icon !== null ? '<i class="' . $this->printHtml($tag->icon ?? '') . '"></i>' : ''; ?><?= $this->printHtml($tag->getL11n()); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($editable) : ?>
                    <div class="col-xs-6 end-xs plain-grid">
                        <a tabindex="0" class="button" href="<?= UriFactory::build('editor/edit?id=' . $doc->getId()); ?>"><?= $this->getHtml('Edit', '0', '0'); ?></a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if (!empty($files)) : ?>
            <div class="portlet-foot">
                <ul>
                    <?php foreach ($files as $file) :
                        $url = $file->extension === 'collection'
                                ? UriFactory::build('media/list?path=' . \rtrim($file->getVirtualPath(), '/') . '/' . $file->name)
                                : UriFactory::build('media/single?id=' . $file->getId()
                                    . '&path={?path}' . (
                                            $file->getId() === 0
                                                ? '/' . $file->name
                                                : ''
                                        )
                                );
                    ?>
                        <li><a class="content" href="<?= $url; ?>"><?= $this->printHtml($file->name); ?></a>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </section>
    </div>
</div>
