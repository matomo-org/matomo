<?php

/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Library
 */

require_once PIWIK_INCLUDE_PATH . '/libs/tcpdf/tcpdf.php';

class Piwik_TCPDF extends TCPDF
{
    protected $footerContent = null;
    protected $currentPageNo = null;

    function Footer()
    {
        //Don't show footer on the frontPage
        if ($this->currentPageNo > 1) {
            $this->SetY(-15);
            $this->SetFont($this->footer_font[0], $this->footer_font[1], $this->footer_font[2]);
            $this->Cell(0, 10, $this->footerContent . Piwik_Translate('PDFReports_Pagination', array($this->getAliasNumPage(), $this->getAliasNbPages())), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }
    }

    function setCurrentPageNo()
    {
        if (empty($this->currentPageNo)) {
            $this->currentPageNo = 1;
        } else {
            $this->currentPageNo++;
        }
    }

    function AddPage($orientation = '')
    {
        parent::AddPage($orientation);
        $this->setCurrentPageNo();
    }

    function SetFooterContent($footerContent)
    {
        $this->footerContent = $footerContent;
    }

}