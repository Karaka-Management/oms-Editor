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

use phpOMS\Uri\UriFactory;

/** @var \Modules\Editor\Models\EditorDoc $doc */
$doc   = $this->data['doc'];
$files = $doc->getMedia();

/** @var bool $editable */
$editable = $this->data['editable'];

/** @var \Modules\Tag\Models\Tag[] $tag */
$tags = $doc->getTags();

/** @var \phpOMS\Views\View $this */
echo $this->data['nav']->render(); ?>
<div class="row">
    <div class="col-xs-12">
        <section class="portlet">
            <div class="portlet-head"><?= $this->printHtml($doc->title); ?></div>
            <div class="portlet-body">
                <article><?= $doc->content; ?></article>
                <?php if (!empty($tags)) : ?>
                    <?php foreach ($tags as $tag) : ?>
                        <span class="tag" style="background: <?= $this->printHtml($tag->color); ?>"><?= empty($tag->icon) ? '' : '<i class="' . $this->printHtml($tag->icon) . '"></i>'; ?><?= $this->printHtml($tag->getL11n()); ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php if (!empty($files)) : ?>
                    <div>
                        <?php foreach ($files as $media) : ?>
                            <span><a class="content" href="<?= UriFactory::build('{/base}/media/single?id=' . $media->id);?>"><?= $media->name; ?></a></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($editable) : ?>
            <div class="portlet-foot">
                <a tabindex="0" class="button" href="<?= UriFactory::build('{/base}/editor/edit?id=' . $doc->id); ?>"><?= $this->getHtml('Edit', '0', '0'); ?></a>
            </div>
            <?php endif; ?>
        </section>
    </div>
</div>
