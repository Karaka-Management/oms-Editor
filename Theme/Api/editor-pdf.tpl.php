<?php
/**
 * Jingga
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
$pdf = new DefaultPdf();

$pdf->setHeaderData(
    $this->getData('defaultTemplates')->findFile('logo.png')->getAbsolutePath(), 15,
    $this->getData('logo_name') ?? 'Jingga',
    $this->getData('slogan') ?? 'Business solutions made simple.'
);
$pdf->setCreator($this->getData('creator') ?? 'Jingga');
$pdf->setAuthor($this->getData('creator') ?? 'Jingga');
$pdf->setTitle($this->getData('title') ?? $bill->type->getL11n());
$pdf->setSubject($this->getData('subtitle') ?? '');
$pdf->setKeywords(\implode(', ', $this->getData('keywords') ?? []));
$pdf->language = $bill->language;

$pdf->attributes['legal_name'] = $this->getData('legal_company_name') ?? 'Jingga e.K.';
$pdf->attributes['address']    = $this->getData('company_address') ?? 'Kirchstr. 33';
$pdf->attributes['city']       = $this->getData('company_city') ?? '61191 Rosbach';

$pdf->attributes['ceo']        = $this->getData('company_ceo') ?? 'Dennis Eichhorn';
$pdf->attributes['tax_office'] = $this->getData('company_tax_office') ?? 'HRB ???';
$pdf->attributes['tax_number'] = $this->getData('company_tax_id') ?? '123456789';

$pdf->attributes['bank_name']    = $this->getData('company_bank_name') ?? 'Volksbank Mittelhessen';
$pdf->attributes['swift']        = $this->getData('company_swift') ?? '.....';
$pdf->attributes['bank_account'] = $this->getData('company_bank_account') ?? '.....';

$pdf->attributes['website'] = $this->getData('company_website') ?? 'www.jingga.app';
$pdf->attributes['email']   = $this->getData('company_email') ?? 'info@jingga.app';
$pdf->attributes['phone']   = $this->getData('company_phone') ?? '+49 0152 ????';

$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// add a page
$pdf->AddPage();
$topPos = $pdf->getY();

$doc = $this->getData('doc') ?? new NullEditorDoc();

$pdf->writeHTML(Markdown::parse($doc->plain));

//Close and output PDF document
$pdf->Output(
    $this->getData('path') ?? ($doc->createdAt->format('Y-m-d') . '_' . $doc->id . '.pdf'),
    'I'
);
