<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Framework\Mock;

class Promo extends \Piwik\Plugins\ProfessionalServices\Promo
{
    public function getLinkTitle()
    {
        return $this->linkTitles[0];
    }

    public function getContent()
    {
        return $this->content[0];
    }
}
