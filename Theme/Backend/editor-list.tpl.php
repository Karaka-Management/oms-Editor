<?php
/**
 * Orange Management
 *
 * PHP Version 7.4
 *
 * @package   Modules\Editor
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

use phpOMS\Uri\UriFactory;

/**
 * @var \phpOMS\Views\View                 $this
 * @var \Modules\Editor\Models\EditorDoc[] $docs
 */
$docs = $this->getData('docs');

/** @var \Modules\Media\Models\Collection[] */
$collections = $this->getData('collections');

$mediaPath = \urldecode($this->getData('path') ?? '/');

$previous = empty($docs) ? '{/prefix}editor/list' : '{/prefix}editor/list?{?}&id=' . \reset($docs)->getId() . '&ptype=p';
$next     = empty($docs) ? '{/prefix}editor/list' : '{/prefix}editor/list?{?}&id=' . \end($docs)->getId() . '&ptype=n';

$docs = $this->getData('docs');

echo $this->getData('nav')->render(); ?>
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <ul class="crumbs-2">
                <li data-href="<?= UriFactory::build('{/prefix}editor/list?path=/'); ?>"><a href="<?= UriFactory::build('{/prefix}editor/list?path=/'); ?>">/</a></li>
                <?php
                    $subPath = '';
                    $paths   = \explode('/', \ltrim($mediaPath, '/'));
                    $length  = \count($paths);

                    for ($i = 0; $i < $length; ++$i) :
                        if ($paths[$i] === '') {
                            continue;
                        }

                        $subPath .= '/' . $paths[$i];

                        $url = UriFactory::build('{/prefix}editor/list?path=' . $subPath);
                ?>
                    <li data-href="<?= $url; ?>"<?= $i === $length - 1 ? 'class="active"' : ''; ?>><a href="<?= $url; ?>"><?= $this->printHtml($paths[$i]); ?></a></li>
                <?php endfor; ?>
            </ul>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Documents'); ?><i class="fa fa-download floatRight download btn"></i></div>
            <table class="default">
            <thead>
            <tr>
                <td>
                <td class="wf-100"><?= $this->getHtml('Title'); ?>
                <td><?= $this->getHtml('Creator'); ?>
                <td><?= $this->getHtml('Created'); ?>
            <tbody>
            <?php $count = 0; foreach ($collections as $key => $value) : ++$count;
                $url     = UriFactory::build('{/prefix}editor/list?path=' . \rtrim($value->getVirtualPath(), '/') . '/' . $value->getName());
            ?>
                <tr data-href="<?= $url; ?>">
                    <td><a href="<?= $url; ?>"><i class="fa fa-folder-o"></i></a>
                    <td><a href="<?= $url; ?>"><?= $this->printHtml($value->getName()); ?></a>
                    <td><a href="<?= $url; ?>"><?= $this->printHtml($value->getCreatedBy()->getName1()); ?></a>
                    <td><a href="<?= $url; ?>"><?= $this->printHtml($value->getCreatedAt()->format('Y-m-d')); ?></a>
            <?php endforeach; ?>
            <?php foreach ($docs as $key => $value) : ++$count;
            $url         = UriFactory::build('{/prefix}editor/single?{?}&id=' . $value->getId()); ?>
                <tr tabindex="0" data-href="<?= $url; ?>">
                    <td><i class="fa fa-file-o"></i>
                    <td data-label="<?= $this->getHtml('Title'); ?>"><a href="<?= $url; ?>"><?= $this->printHtml($value->getTitle()); ?></a>
                    <td data-label="<?= $this->getHtml('Creator'); ?>"><a href="<?= $url; ?>"><?= $this->printHtml($value->getCreatedBy()->getName1()); ?></a>
                    <td data-label="<?= $this->getHtml('Created'); ?>"><a href="<?= $url; ?>"><?= $this->printHtml($value->getCreatedAt()->format('Y-m-d H:i:s')); ?></a>
            <?php endforeach; ?>
            <?php if ($count === 0) : ?>
                <tr><td colspan="3" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
            <?php endif; ?>
        </table>
        <div class="portlet-foot">
            <a tabindex="0" class="button" href="<?= UriFactory::build($previous); ?>"><?= $this->getHtml('Previous', '0', '0'); ?></a>
            <a tabindex="0" class="button" href="<?= UriFactory::build($next); ?>"><?= $this->getHtml('Next', '0', '0'); ?></a>
        </div>
    </div>
</div>
