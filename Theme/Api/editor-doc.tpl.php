<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Media
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use Modules\Editor\Models\NullEditorDoc;
use phpOMS\Utils\Parser\Markdown\Markdown;

/** @var \phpOMS\Views\View $this */
require_once $this->getData('defaultTemplates')
	->findFile('.pdf.php')
	->getAbsolutePath();

/** @var \Modules\Editor\Models\EditorDoc $doc */
$doc = $this->getData('doc') ?? new NullEditorDoc();

// Set up default pdf template
/** @phpstan-import-type DefaultPdf from ../../../../Admin/Install/Media/PdfDefaultTemplate/pdfTemplate.pdf.php */
$word = new DefaultWord();
$section = $word->createFirstPage();

\PhpOffice\PhpWord\Shared\Html::addHtml($section, Markdown::parse($doc->plain));

$file = \tempnam(\sys_get_temp_dir(), 'oms_');
$word->save($file, 'Word2007');

$content = \file_get_contents($file);
if ($content !== false) {
    echo $content;
}

\unlink($file);
