<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CustomDimensions\Dimension;

use \Exception;
use Piwik\Piwik;
use Piwik\Plugins\CustomDimensions\Dao\Configuration;

class Dimension
{
    /**
     * @var int
     */
    private $idDimension;

    /**
     * @var int
     */
    private $idSite;

    /**
     * @var array
     */
    private $dimension;

    public function __construct($idDimension, $idSite)
    {
        $this->idDimension = $idDimension;
        $this->idSite      = $idSite;
        $this->dimension   = $this->getConfiguration()->getCustomDimension($idDimension, $idSite);
    }

    public function checkExists()
    {
        if (empty($this->dimension)) {
            $msg = Piwik::translate('CustomDimensions_ExceptionDimensionDoesNotExist', array($this->idDimension, $this->idSite));
            throw new Exception($msg);
        }
    }

    public function checkActive()
    {
        $this->checkExists();

        if (empty($this->dimension['active'])) {
            $msg = Piwik::translate('CustomDimensions_ExceptionDimensionIsNotActive', array($this->idDimension, $this->idSite));
            throw new Exception($msg);
        }
    }

    public function getScope()
    {
        $this->checkExists();

        return $this->dimension['scope'];
    }

    public function getCaseSensitive()
    {
        $this->checkExists();

        return $this->dimension['case_sensitive'];
    }

    private function getConfiguration()
    {
        return new Configuration();
    }

}