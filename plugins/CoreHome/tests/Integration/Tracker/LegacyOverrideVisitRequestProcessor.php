<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Integration\Tracker;

use Piwik\Plugins\CoreHome\Tracker\VisitRequestProcessor;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit\VisitProperties;

/**
 * A plugin may replace the visit request processor through dependency injection and override
 * lastUserIdWasSetAndDoesMatch() with the signature that method has always had.
 *
 * PHP refuses to load an override whose signature no longer matches its parent, and the tracker
 * would stop rather than degrade, so the signature is part of the plugin-facing surface. This
 * class holds it: giving the method another parameter stops this file loading at all.
 */
class LegacyOverrideVisitRequestProcessor extends VisitRequestProcessor
{
    /**
     * @var bool
     */
    public $lastUserIdWasChecked = false;

    protected function lastUserIdWasSetAndDoesMatch(VisitProperties $visitProperties, Request $request)
    {
        $this->lastUserIdWasChecked = true;

        return parent::lastUserIdWasSetAndDoesMatch($visitProperties, $request);
    }
}
