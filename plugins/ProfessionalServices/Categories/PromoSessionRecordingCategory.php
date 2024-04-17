<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\Categories;

use Piwik\Category\Category;

class PromoSessionRecordingCategory extends Category
{
    protected $id = 'ProfessionalServices_PromoSessionRecording';
    protected $order = 59;
    protected $icon = 'icon-play';
}
