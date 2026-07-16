<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions\Dimension;

use Exception;
use Piwik\Piwik;

class Description
{
    /**
     * @var string
     */
    private $description;

    public function __construct($description)
    {
        $this->description = (string) $description;
    }

    public function check(): void
    {
        $maxLen = 1000;

        if (mb_strlen($this->description) > $maxLen) {
            throw new Exception(Piwik::translate('CustomDimensions_DescriptionIsTooLong', $maxLen));
        }

        if (strip_tags($this->description) !== $this->description) {
            throw new Exception(Piwik::translate('CustomDimensions_DescriptionAllowedCharacters'));
        }
    }
}
