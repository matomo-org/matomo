<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use Exception;

/**
 * TCPDF class wrapper.
 *
 */
class TCPDF extends \TCPDF
{
    protected $footerContent = null;
    protected $currentPageNo = null;

    /**
     * Render page footer
     *
     * @see TCPDF::Footer()
     */
    public function Footer()
    {
        //Don't show footer on the frontPage
        if ($this->currentPageNo > 1) {
            $this->SetY(-15);
            $this->SetFont($this->footer_font[0], $this->footer_font[1], $this->footer_font[2]);
            $this->Cell(0, 10, $this->footerContent . Piwik::translate('ScheduledReports_Pagination', array($this->getAliasNumPage(), $this->getAliasNbPages())), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }
    }

    /**
     * @see TCPDF::Error()
     * @param $msg
     * @throws Exception
     */
    public function Error($msg)
    {
        $this->_destroy(true);
        throw new Exception($msg);
    }

    /**
     * Set current page number
     */
    public function setCurrentPageNo()
    {
        if (empty($this->currentPageNo)) {
            $this->currentPageNo = 1;
        } else {
            $this->currentPageNo++;
        }
    }

    /**
     * Add page to document
     *
     * @see TCPDF::AddPage()
     *
     * @param string $orientation
     * @param mixed $format
     * @param bool $keepmargins
     * @param bool $tocpage
     */
    public function AddPage($orientation = '', $format = '', $keepmargins = false, $tocpage = false)
    {
        parent::AddPage($orientation);
        $this->setCurrentPageNo();
    }

    /**
     * Set footer content
     *
     * @param string $footerContent
     */
    public function SetFooterContent($footerContent)
    {
        $this->footerContent = $footerContent;
    }
}
