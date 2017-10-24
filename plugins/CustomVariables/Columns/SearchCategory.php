<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomVariables\Columns;

use Piwik\Columns\Discriminator;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugins\Actions\Actions\ActionSiteSearch;

class SearchCategory extends ActionDimension
{
    protected $type = self::TYPE_TEXT;
    protected $columnName = 'custom_var_v4';
    protected $nameSingular = 'Actions_SiteSearchCategory';
    protected $namePlural = 'Actions_SiteSearchCategories';

    public function getDbDiscriminator()
    {
        return new Discriminator($this->dbTableName, 'custom_var_k4', ActionSiteSearch::CVAR_KEY_SEARCH_CATEGORY);
    }
}