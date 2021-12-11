<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
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
$docs = $this->getData('docs') ?? [];

/** @var \Modules\Media\Models\Collection[] */
$collections = $this->getData('collections');
$mediaPath   = \urldecode($this->getData('path') ?? '/');
$account     = $this->getData('account');

$accountDir = $account->getId() . ' ' . $account->login;

$previous = empty($docs) ? '{/prefix}editor/list' : '{/prefix}editor/list?{?}&id=' . \reset($docs)->getId() . '&ptype=p';
$next     = empty($docs) ? '{/prefix}editor/list' : '{/prefix}editor/list?{?}&id=' . \end($docs)->getId() . '&ptype=n';

$docs = $this->getData('docs');

echo $this->getData('nav')->render(); ?>
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <ul class="crumbs-2">
                <li data-href="<?= UriFactory::build('{/prefix}editor/list?path=/Accounts/' . $accountDir); ?>"><a href="<?= UriFactory::build('{/prefix}editor/list?path=/Accounts/' . $accountDir); ?>"><i class="fa fa-home"></i></a>
                <li data-href="<?= UriFactory::build('{/prefix}editor/list?path=/'); ?>"><a href="<?= UriFactory::build('{/prefix}editor/list?path=/'); ?>">/</a></li>
                <?php
                    $subPath    = '';
                    $paths      = \explode('/', \ltrim($mediaPath, '/'));
                    $length     = \count($paths);
                    $parentPath = '';

                    for ($i = 0; $i < $length; ++$i) :
                        if ($paths[$i] === '') {
                            continue;
                        }

                        if ($i === $length - 1) {
                            $parentPath = $subPath === '' ? '/' : $subPath;
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
            <div class="slider">
            <table id="editorList" class="default sticky">
            <thead>
            <tr>
                <td><label class="checkbox" for="editorList-0">
                        <input type="checkbox" id="editorList-0" name="editorselect">
                        <span class="checkmark"></span>
                    </label>
                <td>
                <td class="wf-100"><?= $this->getHtml('Title'); ?>
                    <label for="editorList-sort-1">
                            <input type="radio" name="editorList-sort" id="editorList-sort-1">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="editorList-sort-2">
                            <input type="radio" name="editorList-sort" id="editorList-sort-2">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
                <td><?= $this->getHtml('Creator'); ?>
                    <label for="editorList-sort-3">
                            <input type="radio" name="editorList-sort" id="editorList-sort-3">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="editorList-sort-4">
                            <input type="radio" name="editorList-sort" id="editorList-sort-4">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
                <td><?= $this->getHtml('Created'); ?>
                    <label for="editorList-sort-5">
                            <input type="radio" name="editorList-sort" id="editorList-sort-5">
                            <i class="sort-asc fa fa-chevron-up"></i>
                        </label>
                        <label for="editorList-sort-6">
                            <input type="radio" name="editorList-sort" id="editorList-sort-6">
                            <i class="sort-desc fa fa-chevron-down"></i>
                        </label>
                        <label>
                            <i class="filter fa fa-filter"></i>
                        </label>
            <tbody>
            <?php if (!empty($parentPath)) : $url = UriFactory::build('{/prefix}editor/list?path=' . $parentPath); ?>
                        <tr tabindex="0" data-href="<?= $url; ?>">
                            <td>
                            <td data-label="<?= $this->getHtml('Type'); ?>"><a href="<?= $url; ?>"><i class="fa fa-folder-open-o"></i></a>
                            <td data-label="<?= $this->getHtml('Name'); ?>"><a href="<?= $url; ?>">..
                            </a>
                            <td>
                            <td>
            <?php endif; ?>
            <?php $count = 0; foreach ($collections as $key => $value) : ++$count;
                $url     = UriFactory::build('{/prefix}editor/list?path=' . \rtrim($value->getVirtualPath(), '/') . '/' . $value->name);
            ?>
                <tr data-href="<?= $url; ?>">
                    <td><label class="checkbox" for="editorList-<?= $key; ?>">
                                <input type="checkbox" id="editorList-<?= $key; ?>" name="editorselect">
                                <span class="checkmark"></span>
                            </label>
                    <td><a href="<?= $url; ?>"><i class="fa fa-folder-open-o"></i></a>
                    <td><a href="<?= $url; ?>"><?= $this->printHtml($value->name); ?></a>
                    <td><a class="content" href="<?= UriFactory::build('{/prefix}profile/single?{?}&for=' . $value->createdBy->getId()); ?>"><?= $this->printHtml($value->createdBy->name1); ?></a>
                    <td><a href="<?= $url; ?>"><?= $this->printHtml($value->createdAt->format('Y-m-d')); ?></a>
            <?php endforeach; ?>
            <?php foreach ($docs as $key => $value) : ++$count;
            $url         = UriFactory::build('{/prefix}editor/single?{?}&id=' . $value->getId()); ?>
                <tr tabindex="0" data-href="<?= $url; ?>">
                    <td><label class="checkbox" for="editorList-<?= $key; ?>">
                                <input type="checkbox" id="editorList-<?= $key; ?>" name="editorselect">
                                <span class="checkmark"></span>
                            </label>
                    <td><i class="fa fa-file-o"></i>
                    <td data-label="<?= $this->getHtml('Title'); ?>"><a href="<?= $url; ?>"><?= $this->printHtml($value->title); ?></a>
                    <td data-label="<?= $this->getHtml('Creator'); ?>"><a class="content" href="<?= UriFactory::build('{/prefix}profile/single?{?}&for=' . $value->createdBy->getId()); ?>"><?= $this->printHtml($value->createdBy->name1); ?></a>
                    <td data-label="<?= $this->getHtml('Created'); ?>"><a href="<?= $url; ?>"><?= $this->printHtml($value->createdAt->format('Y-m-d')); ?></a>
            <?php endforeach; ?>
            <?php if ($count === 0) : ?>
                <tr><td colspan="4" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
            <?php endif; ?>
        </table>
        </div>
        <div class="portlet-foot">
            <a tabindex="0" class="button" href="<?= UriFactory::build($previous); ?>"><?= $this->getHtml('Previous', '0', '0'); ?></a>
            <a tabindex="0" class="button" href="<?= UriFactory::build($next); ?>"><?= $this->getHtml('Next', '0', '0'); ?></a>
        </div>
    </div>
</div>
