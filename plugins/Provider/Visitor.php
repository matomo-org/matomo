<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Provider;

use Piwik\Piwik;

require_once PIWIK_INCLUDE_PATH . '/plugins/Provider/functions.php';

class Visitor
{
    private $details = array();

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function getProvider()
    {
        if (isset($this->details['location_provider'])) {
            return $this->details['location_provider'];
        }
        return Piwik::translate('General_Unknown');
    }

    public function getProviderName()
    {
        return getPrettyProviderName($this->getProvider());
    }

    public function getProviderUrl()
    {
        return getHostnameUrl(@$this->details['location_provider']);
    }
}