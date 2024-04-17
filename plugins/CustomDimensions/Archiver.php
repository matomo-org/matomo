<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions;

/**
 * Archives reports for each active Custom Dimension of a website.
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    const LABEL_CUSTOM_VALUE_NOT_DEFINED = "Value not defined";

    public static function buildRecordNameForCustomDimensionId($id)
    {
        return 'CustomDimensions_Dimension' . (int) $id;
    }
}
