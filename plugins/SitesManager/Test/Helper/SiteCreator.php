<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SitesManager\Test\Helper;

use Piwik\Common;
use Piwik\Db;

class SiteCreator
{
    /**
     * @param array $site
     * @return int
     */
    public function createSite(array $site)
    {
        $defaultValues = array(
            'ts_created' => date('Y-m-d H:i:s'),
            'ecommerce' => '0',
            'sitesearch' => '1',
            'sitesearch_keyword_parameters' => '',
            'sitesearch_category_parameters' => '',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'excluded_ips' => '',
            'excluded_parameters' => '',
            'excluded_user_agents' => '',
            'group' => '',
            'type' => 'website',
            'keep_url_fragment' => '0'
        );

        $site = array_merge($defaultValues, $site);

        Db::get()->insert(Common::prefixTable('site'), $site);

        return Db::get()->lastInsertId();
    }
} 
