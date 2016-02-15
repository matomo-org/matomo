<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Framework\Mock\PiwikPro;

class Advertising extends \Piwik\PiwikPro\Advertising
{
    public function __construct()
    {
    }

    public function arePiwikProAdsEnabled()
    {
        return true;
    }
}