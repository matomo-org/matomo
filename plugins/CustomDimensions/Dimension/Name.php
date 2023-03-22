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

class Name
{
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function check()
    {
        $maxLen = 255;

        if (empty($this->name)) {
            throw new Exception(Piwik::translate('CustomDimensions_NameIsRequired'));
        }

        if (strlen($this->name) > $maxLen) {
            throw new Exception(Piwik::translate('CustomDimensions_NameIsTooLong', $maxLen));
        }

        $blockedCharacters = self::getBlockedCharacters();

        // we do not really have to do this and it is not very effective for preventing XSS but doesn't hurt to have
        if (strip_tags($this->name) !== $this->name || str_replace($blockedCharacters, '', $this->name) !== $this->name) {
            throw new Exception(Piwik::translate('CustomDimensions_NameAllowedCharacters'));
        }
    }

    /**
     * @api
     */
    public static function getBlockedCharacters()
    {
        return [
            '/', '\\', '&', '.', '<', '>',
        ];
    }
}
