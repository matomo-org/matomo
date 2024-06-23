<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\Mock;

/**
 * FakeSite for UnitTests
 * @since 2.13.0
 */
class Site extends \Piwik\Site
{
    /**
     * Constructor.
     *
     * @param int $idsite The ID of the site we want data for.
     */
    public function __construct($idsite)
    {
        $this->id = (int)$idsite;
    }

    public function getTimezone()
    {
        return 'UTC';
    }
}
