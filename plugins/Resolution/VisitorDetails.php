<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Resolution;

use Piwik\Plugins\Live\VisitorDetailsAbstract;

class VisitorDetails extends VisitorDetailsAbstract
{
    public function extendVisitorDetails(&$visitor)
    {
        $visitor['resolution'] = $this->getResolution();
    }

    protected function getResolution()
    {
        if (!array_key_exists('config_resolution', $this->details)) {
            return null;
        }

        return $this->details['config_resolution'];
    }
}