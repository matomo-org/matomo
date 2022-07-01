<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings\Storage\Backend;

use Piwik\Plugins\SitesManager\Model;
use Piwik\Site;
use Exception;

/**
 * Backend for an existing site. Stores all settings in the "site" database table.
 */
class SitesTable implements BackendInterface
{
    /**
     * @var int
     */
    private $idSite;

    private $commaSeparatedArrayFields = array(
        'sitesearch_keyword_parameters',
        'sitesearch_category_parameters',
        'excluded_user_agents',
        'excluded_parameters',
        'excluded_ips',
        'excluded_referrers'
    );

    // these fields are standard fields of a site and cannot be adjusted via a setting
    private $allowedNames = array(
        'ecommerce', 'sitesearch', 'sitesearch_keyword_parameters',
        'sitesearch_category_parameters', 'exclude_unknown_urls',
        'excluded_ips', 'excluded_parameters', 'excluded_referrers',
        'excluded_user_agents', 'keep_url_fragment', 'urls'
    );

    public function __construct($idSite)
    {
        if (empty($idSite)) {
            throw new Exception('No idSite given for Measurable backend');
        }

        $this->idSite = (int) $idSite;
    }

    /**
     * @inheritdoc
     */
    public function getStorageId()
    {
        return 'SitesTable_' . $this->idSite;
    }

    /**
     * Saves (persists) the current setting values in the database.
     */
    public function save($values)
    {
        $model = $this->getModel();

        foreach ($values as $key => $value) {
            if (!in_array($key, $this->allowedNames)) {
                unset($values[$key]);
                continue;
            }

            if (is_array($value) && in_array($key, $this->commaSeparatedArrayFields)) {
                $values[$key] = implode(',', $value);
            } elseif (is_bool($value)) {
                $values[$key] = (int) $value;
            }
        }

        if (!empty($values['urls'])) {
            $urls = array_unique($values['urls']);
            $values['main_url'] = array_shift($urls);

            $model->deleteSiteAliasUrls($this->idSite);
            foreach ($urls as $url) {
                $model->insertSiteUrl($this->idSite, $url);
            }
        }

        unset($values['urls']);

        $model->updateSite($values, $this->idSite);
        Site::clearCacheForSite($this->idSite);
    }

    public function load()
    {
        if (!empty($this->idSite)) {
            $site = Site::getSite($this->idSite);

            $urls = $this->getModel();
            $site['urls'] = $urls->getSiteUrlsFromId($this->idSite);

            foreach ($this->commaSeparatedArrayFields as $field) {
                if (!empty($site[$field]) && is_string($site[$field])) {
                    $site[$field] = explode(',', $site[$field]);
                }
            }

            return $site;
        }
    }

    private function getModel()
    {
        return new Model();
    }

    public function delete()
    {
    }

}
